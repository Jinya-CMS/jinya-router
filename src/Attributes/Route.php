<?php

namespace Jinya\Router\Attributes;

use Attribute;

/**
 * Marks a method as route for the routing table generator
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    /**
     * Creates a new instance of the route attribute
     *
     * @param HttpMethod $httpMethod The HTTP method this method is called for
     * @param string $route The route this method is called for
     */
    public function __construct(public readonly HttpMethod $httpMethod = HttpMethod::GET, public readonly string $route = '')
    {
    }
}