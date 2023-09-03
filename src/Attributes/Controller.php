<?php

namespace Jinya\Router\Attributes;

use Attribute;

/**
 * Declares a class as controller, controllers may contain several routes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    /**
     * Creates a new instance of the controller attribute
     *
     * @param string $route The base route of the controller, the route generator will prepend this route to all other routes.
     */
    public function __construct(public readonly string $route = '')
    {
    }
}
