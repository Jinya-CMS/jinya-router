<?php

namespace Jinya\Router;

use Jinya\Router\Extensions\Extension;
use Jinya\Router\Router\Router;
use ReflectionException;

/**
 * Builds the routing table cache and overwrites the currently active table. This should only be called from CLI commands, since the generation of the table can take some time
 *
 * @param string $cacheDirectory
 * @param string $controllerDirectory
 * @param Extension ...$extensions
 * @return void
 * @throws ReflectionException
 */
function build_routing_table(
    string $cacheDirectory,
    string $controllerDirectory,
    Extension ...$extensions
): void {
    Router::buildRoutingCache($cacheDirectory, $controllerDirectory, ...$extensions);
}
