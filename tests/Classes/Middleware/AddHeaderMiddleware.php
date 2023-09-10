<?php

namespace Jinya\Router\Tests\Classes\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddHeaderMiddleware implements MiddlewareInterface
{
    public function __construct(public readonly string $headerToAdd = 'TestHeader', public readonly int $times = 1)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request)->withHeader($this->headerToAdd, 'X-Test');
    }
}
