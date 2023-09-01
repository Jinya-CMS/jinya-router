<?php

namespace Jinya\Router;

use FastRoute\Dispatcher;
use Jinya\Router\Extensions\Extension;
use Jinya\Router\Http\ControllerMiddleware;
use Jinya\Router\Http\FunctionMiddleware;
use Jinya\Router\Router\Router;
use Jinya\Router\Templates\Engine;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipe;
use Nyholm\Psr7\Response;
use ReflectionException;
use Throwable;

/**
 * Handles the current request, simply call this function in your router script, and the rest will just work™
 *
 * @param string $cacheDirectory The directory the cache file should be written to
 * @param string $controllerDirectory The directory the controllers reside in
 * @param Engine|null $engine The template engine to be used by the base controller, if null no template engine will be used
 * @param Extension ...$extensions The routing table generator extensions to use
 * @return void
 * @throws ReflectionException
 */
function handle_request(string $cacheDirectory, string $controllerDirectory, Engine|null $engine = null, Extension ...$extensions): void
{
    $router = new Router($cacheDirectory, $controllerDirectory, ...$extensions);
    $router->prepareRoutingCache(false);

    $dispatcher = include $router->getCacheFile();

    $routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    $app = new MiddlewarePipe();
    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            $app->pipe(new FunctionMiddleware(static function () {
                return new Response(404);
            }, []));
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = implode(',', $routeInfo[1]);
            $app->pipe(new FunctionMiddleware(static function (string $allowedMethods) {
                return new Response(405, ['Allow' => $allowedMethods]);
            }, [$allowedMethods]));
            break;
        case Dispatcher::FOUND:
            [, $handler, $vars] = $routeInfo;
            $type = $handler[0];
            if ($type === 'fn') {
                [, $function, $middlewares] = $handler;
                if (is_string($function) && function_exists($function)) {
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $app->pipe($middleware);
                        }
                    }
                    $app->pipe(new FunctionMiddleware($function, $vars));
                }
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
            return ServerRequestFactory::fromGlobals();
        },
        static function (Throwable $e) {
            $response = (new ResponseFactory())->createResponse(500);
            $response->getBody()->write(sprintf(
                'An error occurred: %s',
                $e->getMessage()
            ));
            return $response;
        });
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
function build_routing_cache(string $cacheDirectory, string $controllerDirectory, Extension ...$extensions): void
{
    $router = new Router($cacheDirectory, $controllerDirectory, ...$extensions);
    $router->prepareRoutingCache(true);
}
