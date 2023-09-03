<?php

namespace Jinya\Router\Http;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A middleware defining a function as callable for a http request
 * @internal
 */
class FunctionMiddleware implements MiddlewareInterface
{
    /**
     * Creates a new instance of the function middleware
     * @param mixed $function The function to call during processing of the request
     * @param array<mixed> $vars The variables passed to the function
     */
    public function __construct(public mixed $function, public readonly array $vars)
    {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $function = $this->function;
        if (is_callable($function) || (is_string($function) && function_exists($function))) {
            return $function(...$this->vars);
        }

        return new Response(404);
    }
}
