<?php

namespace Jinya\Router\Router;

use DirectoryIterator;
use FastRoute\Dispatcher;
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Middlewares;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Extensions\Extension;
use Jinya\Router\Http\ControllerMiddleware;
use Jinya\Router\Http\FunctionMiddleware;
use Jinya\Router\Templates\Engine;
use Laminas\Diactoros\ResponseFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipe;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;

use function Laminas\Stratigility\middleware;

/**
 * The router that actually handles requests
 */
class Router
{
    /** @var string The file path of the cache */
    private string $cacheFile;

    /** @var Extension[] The extensions for the generation */
    private array $extensions;

    /**
     * Creates a new routing table generator
     *
     * @param string $cacheDirectory The directory the cache resides in
     * @param string $controllerDirectory The directory the controllers reside in
     * @param Extension ...$extensions The extensions to use during building the routing table
     */
    private function __construct(
        string $cacheDirectory,
        private readonly string $controllerDirectory,
        Extension ...$extensions
    ) {
        $routingCacheBaseDir = $cacheDirectory . DIRECTORY_SEPARATOR . 'routing' . DIRECTORY_SEPARATOR;
        if (!@mkdir($routingCacheBaseDir, recursive: true) && !is_dir($routingCacheBaseDir)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(sprintf('Directory "%s" was not created', $routingCacheBaseDir));
            // @codeCoverageIgnoreEnd
        }

        $this->extensions = $extensions;
        $this->cacheFile = $routingCacheBaseDir . 'jinya-router.php';
    }

    /**
     * Gets the current cache file
     *
     * @return string
     */
    private function getCacheFile(): string
    {
        return $this->cacheFile;
    }

    /**
     * Prepares the routing table and writes it to the cache file
     *
     * @param bool $force Force the generation of the routing table, this will overwrite the current routing table
     * @return void
     * @throws ReflectionException
     */
    private function prepareRoutingCache(bool $force = false): void
    {
        $cacheFileExists = file_exists($this->cacheFile);
        $recreate = !$cacheFileExists || $force;
        if (!$recreate) {
            foreach ($this->extensions as $extension) {
                if ($extension->recreateCache()) {
                    $recreate = true;
                    break;
                }
            }
        }

        if ($recreate) {
            $routingTable = $this->buildTable();
            file_put_contents($this->cacheFile, $routingTable);
        }
    }

    /**
     * Builds a routing table based on the controller directory and the extensions
     *
     * @return string
     * @throws ReflectionException
     */
    private function buildTable(): string
    {
        $controllerTable = $this->buildControllerTable();

        $routingTable = "<?php" . PHP_EOL .
            'return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {' . PHP_EOL .
            $controllerTable . PHP_EOL .
            implode(
                PHP_EOL,
                array_map(static fn (Extension $extension) => $extension->additionalRoutes(), $this->extensions)
            ) . PHP_EOL .
            '});';

        foreach ($this->extensions as $extension) {
            $routingTable = $extension->afterGeneration($routingTable);
        }

        return $routingTable;
    }

    /**
     * Builds the controller routing table
     *
     * @return string
     * @throws ReflectionException
     */
    private function buildControllerTable(): string
    {
        /** @var class-string[] $classes */
        $classes = [];
        $routingTable = '';
        $iterator = new DirectoryIterator($this->controllerDirectory);
        foreach ($iterator as $controller) {
            if ($controller->getExtension() === 'php') {
                $classes[] = $this->getClassNameFromFile($controller->getPathname());
            }
        }

        foreach ($this->extensions as $extension) {
            $extension->beforeGeneration($classes);
        }

        foreach ($classes as $class) {
            $reflectionControllerClass = new ReflectionClass($class);
            $controllerAttributes = $reflectionControllerClass->getAttributes(Controller::class);
            if (empty($controllerAttributes)) {
                continue;
            }

            $controllerAttribute = $controllerAttributes[0];
            /** @var Controller $controllerAttributeInstance */
            $controllerAttributeInstance = $controllerAttribute->newInstance();
            $groupRoute = $controllerAttributeInstance->route;

            $methods = '';
            $methodsInController = $reflectionControllerClass->getMethods(ReflectionMethod::IS_PUBLIC);
            $controllerMiddlewares = $this->getMiddlewares($reflectionControllerClass);

            $routePrefix = '/';
            if ($groupRoute === '') {
                $routePrefix = '';
            }

            foreach ($methodsInController as $method) {
                $routeAttributes = $method->getAttributes(Route::class);
                if (!empty($routeAttributes)) {
                    $middlewares = [...$controllerMiddlewares, ...$this->getMiddlewares($method)];
                    foreach ($routeAttributes as $routeAttribute) {
                        /** @var Route $routeAttributeInstance */
                        $routeAttributeInstance = $routeAttribute->newInstance();
                        $middleware = ', [' . implode(',', $middlewares) . ']';
                        if (!empty($routeAttributeInstance->route)) {
                            $methods .= '$r->addRoute("' . $routeAttributeInstance->httpMethod->name . '", "' . $routePrefix . $routeAttributeInstance->route . '", ["ctrl", "' . $class . '","' . $method->name . '"' . $middleware . ']);' . PHP_EOL;
                        } else {
                            $methods .= '$r->addRoute("' . $routeAttributeInstance->httpMethod->name . '", "", ["ctrl", "' . $class . '","' . $method->name . '"' . $middleware . ']);' . PHP_EOL;
                        }
                    }
                }
            }

            $routingTable .= '$r->addGroup("/' . $groupRoute . '", function (\FastRoute\RouteCollector $r) {' . PHP_EOL .
                $methods . PHP_EOL .
                '});' . PHP_EOL;
        }

        return $routingTable;
    }

    /**
     * Gets the full class name of the given file
     *
     * @param string $file The file to get the class name for
     * @return class-string
     */
    private function getClassNameFromFile(string $file): string
    {
        $contents = file_get_contents($file);
        if (!$contents) {
            // @codeCoverageIgnoreStart
            $contents = $file;
            // @codeCoverageIgnoreEnd
        }

        $namespace = '';
        $class = '';

        $gettingNamespace = false;
        $gettingClass = false;

        $tokens = token_get_all($contents);

        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $gettingNamespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $gettingClass = true;
            }

            if ($gettingNamespace === true) {
                if (is_array($token) && $token[0] === T_NAME_QUALIFIED) {
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    $gettingNamespace = false;
                }
            }

            if (($gettingClass === true) && is_array($token) && $token[0] === T_STRING) {
                $class = $token[1];
                break;
            }
        }

        /** @var class-string $classFqdn */
        $classFqdn = $namespace ? $namespace . '\\' . $class : $class;

        return $classFqdn;
    }

    /**
     * Gets the middlewares used for the given method or class
     *
     * @param ReflectionClass|ReflectionMethod $methodOrClass The class or method to get the middlewares for
     * @return string[]
     * @throws ReflectionException
     */
    private function getMiddlewares(ReflectionClass|ReflectionMethod $methodOrClass): array
    {
        $middlewares = [];
        $middlewareAttributes = $methodOrClass->getAttributes(Middlewares::class);
        foreach ($middlewareAttributes as $middlewareAttribute) {
            /** @var Middlewares $methodMiddlewareAttributeInstance */
            $methodMiddlewareAttributeInstance = $middlewareAttribute->newInstance();
            foreach ($methodMiddlewareAttributeInstance->middlewares as $middleware) {
                $middlewareReflectionClass = new ReflectionClass($middleware);
                if ($middlewareReflectionClass->implementsInterface(MiddlewareInterface::class)) {
                    $ctor = $middlewareReflectionClass->getConstructor();
                    $parameter = [];
                    if ($ctor) {
                        $ctorParams = $ctor->getParameters();
                        foreach ($ctorParams as $ctorParam) {
                            if ($middlewareReflectionClass->hasProperty($ctorParam->name)) {
                                $prop = $middlewareReflectionClass->getProperty($ctorParam->name);
                                $val = $prop->getValue($middleware);
                                $parameter[] = var_export($val, true);
                            }
                        }
                    }

                    $reflectionClassName = $middlewareReflectionClass->getName();
                    $middlewares[] = 'new ' . $reflectionClassName . '(' . implode(
                        ',',
                        $parameter
                    ) . ')';
                }
            }
        }

        return $middlewares;
    }

    /**
     * Handles the current request, simply call this function in your router script, and the rest will just work™
     *
     * @param string $cacheDirectory The directory the cache file should be written to
     * @param string $controllerDirectory The directory the controllers reside in
     * @param ResponseInterface $notFoundResponse
     * @param Engine|null $engine The template engine to be used by the base controller, if null no template engine will be used
     * @param Extension ...$extensions The routing table generator extensions to use
     * @return void
     * @throws ReflectionException
     */
    public static function handle(
        string $cacheDirectory,
        string $controllerDirectory,
        ResponseInterface $notFoundResponse,
        Engine|null $engine = null,
        Extension ...$extensions
    ): void {
        $router = new Router($cacheDirectory, $controllerDirectory, ...$extensions);
        $router->prepareRoutingCache();

        $dispatcher = include $router->getCacheFile();

        $routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
        $app = new MiddlewarePipe();
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $app->pipe(
                    middleware(static fn () => $notFoundResponse)
                );
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = implode(',', $routeInfo[1]);
                $app->pipe(
                    middleware(static fn () => new Response(405, ['Allow' => $allowedMethods]))
                );
                break;
            case Dispatcher::FOUND:
                [, $handler, $vars] = $routeInfo;
                $type = $handler[0];
                if ($type === 'fn') {
                    [, $function, $middlewares] = $handler;
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $app->pipe($middleware);
                        }
                    }

                    $app->pipe(new FunctionMiddleware($function, $vars));
                } elseif ($type === 'ctrl') {
                    [, $controller, $method, $middlewares] = $handler;
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $app->pipe($middleware);
                        }
                    }

                    $app->pipe(new ControllerMiddleware($controller, $method, $vars, $engine));
                }
                break;
        }

        $requestHandleRunner = new RequestHandlerRunner(
            $app,
            new SapiEmitter(),
            static function () {
                $psr17Factory = new Psr17Factory();

                $creator = new ServerRequestCreator(
                    $psr17Factory, // ServerRequestFactory
                    $psr17Factory, // UriFactory
                    $psr17Factory, // UploadedFileFactory
                    $psr17Factory  // StreamFactory
                );

                $request = $creator->fromGlobals();
                if (str_starts_with($request->getHeaderLine('Content-Type'), 'application/json') && in_array(
                    $request->getMethod(),
                    ['POST', 'PUT']
                )) {
                    $request->getBody()->rewind();
                    /** @var array<mixed> $decodedBody */
                    $decodedBody = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                    $request = $request->withParsedBody($decodedBody);
                }

                return $request;
            },
            static function (Throwable $e) {
                // @codeCoverageIgnoreStart
                $response = (new ResponseFactory())->createResponse(500);
                $response->getBody()->write(
                    json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR)
                );

                return $response;
                // @codeCoverageIgnoreEnd
            }
        );

        $requestHandleRunner->run();
    }

    /**
     * Builds the routing table cache and overwrites the currently active table. This should only be called from CLI commands, since the generation of the table can take some time
     *
     * @param string $cacheDirectory The directory the cache file should be written to
     * @param string $controllerDirectory The directory the controllers reside in
     * @param Extension ...$extensions The routing table generator extensions to use
     * @return void
     * @throws ReflectionException
     */
    public static function buildRoutingCache(
        string $cacheDirectory,
        string $controllerDirectory,
        Extension ...$extensions
    ): void {
        $router = new Router($cacheDirectory, $controllerDirectory, ...$extensions);
        $router->prepareRoutingCache(true);
    }
}
