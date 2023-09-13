<?php

namespace Jinya\Router;

use Jinya\Router\Extensions\Extension;
use Jinya\Router\Router\Router;
use Jinya\Router\Templates\Engine;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;

/**
 * Handles the current request, simply call this function in your router script, and the rest will just work™
 *
 * @param string $cacheDirectory
 * @param string $controllerDirectory
 * @param ResponseInterface $notFoundResponse
 * @param Engine|null $engine
 * @param Extension ...$extensions
 * @return void
 * @throws ReflectionException
 */
function handle_request(
    string $cacheDirectory,
    string $controllerDirectory,
    ResponseInterface $notFoundResponse,
    Engine|null $engine = null,
    Extension ...$extensions
): void {
    Router::handle($cacheDirectory, $controllerDirectory, $notFoundResponse, $engine, ...$extensions);
}
