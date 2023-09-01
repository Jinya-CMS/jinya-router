<?php

namespace Jinya\Router\Router;

use DirectoryIterator;
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Extensions\Extension;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * @internal
 * Helper class to generate the routing table
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
    public function __construct(string $cacheDirectory, private readonly string $controllerDirectory, Extension ...$extensions)
    {
        $routingCacheBaseDir = $cacheDirectory . DIRECTORY_SEPARATOR . 'routing' . DIRECTORY_SEPARATOR;
        if (!mkdir($routingCacheBaseDir, recursive: true) && !is_dir($routingCacheBaseDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $routingCacheBaseDir));
        }

        $this->extensions = $extensions;
        $this->cacheFile = $routingCacheBaseDir . 'jinya-router.php';
    }

    /**
     * Gets the current cache file
     *
     * @return string
     */
    public function getCacheFile(): string
    {
        return $this->cacheFile;
    }

    /**
     * Builds a routing table based on the controller directory and the extensions
     *
     * @return string
     * @throws ReflectionException
     */
    public function buildTable(): string
    {
        $controllerTable = $this->buildControllerTable();

        $routingTable = "<?php" . PHP_EOL .
            'return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {' . PHP_EOL .
            $controllerTable . PHP_EOL .
            implode(PHP_EOL, array_map(static fn(Extension $extension) => $extension->additionalRoutes(), $this->extensions)) . PHP_EOL .
            '});';

        foreach ($this->extensions as $extension) {
            $extension->afterGeneration($routingTable);
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
                include_once $controller->getPath();
                $classes[] = $this->getClassNameFromFile($controller->getFilename());
            }
        }

        foreach ($this->extensions as $extension) {
            $extension->beforeGeneration($classes);
        }

        foreach ($classes as $class) {
            /** @var class-string $class */
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
                if (empty($routeAttributes)) {
                    continue;
                }

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
            $contents = $file;
        }

        $namespace = '';
        $class = '';

        $getting_namespace = false;
        $getting_class = false;

        foreach (token_get_all($contents) as $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $getting_namespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $getting_class = true;
            }

            // While we're grabbing the namespace name...
            if ($getting_namespace === true) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR], true)) {
                    $namespace .= $token[1];
                } else if ($token === ';') {
                    $getting_namespace = false;
                }
            }

            if (($getting_class === true) && is_array($token) && $token[0] === T_STRING) {
                $class = $token[1];
                break;
            }
        }

        /** @var class-string $classFqdn */
        $classFqdn = $namespace ? $namespace . '\\' . $class : $class;

        return $classFqdn;
    }

    /**
     * Prepares the routing table and writes it to the cache file
     *
     * @param bool $force Force the generation of the routing table, this will overwrite the current routing table
     * @return void
     * @throws ReflectionException
     */
    public function prepareRoutingCache(bool $force): void
    {
        $cacheFileExists = file_exists($this->cacheFile);
        if (!$cacheFileExists || $force) {
            $routingTable = $this->buildTable();
            file_put_contents($this->cacheFile, $routingTable);
        }
    }

    /**
     * Gets the middlewares used for the given method or class
     *
     * @param ReflectionClass|ReflectionMethod $methodOrClass The class or method to get the middlewares for
     * @return array<string, string>
     * @throws ReflectionException
     */
    private function getMiddlewares(ReflectionClass|ReflectionMethod $methodOrClass): array
    {
        $middlewares = [];
        $middlewareAttributes = $methodOrClass->getAttributes();
        foreach ($middlewareAttributes as $middlewareAttribute) {
            $reflectionClassName = $middlewareAttribute->getName();
            $methodMiddlewareAttributeInstance = $middlewareAttribute->newInstance();
            $middlewareAttributeReflectionClass = new ReflectionClass($reflectionClassName);
            if ($middlewareAttributeReflectionClass->implementsInterface(MiddlewareInterface::class)) {
                $ctor = $middlewareAttributeReflectionClass->getConstructor();
                $parameter = [];
                if ($ctor) {
                    $ctorParams = $ctor->getParameters();
                    foreach ($ctorParams as $ctorParam) {
                        if ($middlewareAttributeReflectionClass->hasProperty($ctorParam->name)) {
                            $prop = $middlewareAttributeReflectionClass->getProperty($ctorParam->name);
                            $val = $prop->getValue($methodMiddlewareAttributeInstance);
                            if (is_string($val)) {
                                $parameter[] = "'$val'";
                            } else {
                                $parameter[] = $val;
                            }
                        }
                    }
                }

                $middlewares[$reflectionClassName] = 'new ' . $reflectionClassName . '(' . implode(',', $parameter) . ')';
            }
        }

        return $middlewares;
    }
}