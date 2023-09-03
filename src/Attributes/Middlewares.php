<?php

namespace Jinya\Router\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Declares the middlewares to be used with the given route
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Middlewares
{
    /** @var MiddlewareInterface[] The middlewares to execute with this method */
    public readonly array $middlewares;

    /**
     * Creates an instance of the middleware attribute
     *
     * @param MiddlewareInterface ...$middlewares The middlewares to use with the given route
     */
    public function __construct(MiddlewareInterface ...$middlewares)
    {
        $this->middlewares = $middlewares;
    }
}
