<?php

namespace Jinya\Router\Http;

use Jinya\Router\Templates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * A middleware used to execute a controller and its method in a request
 * @internal
 */
class ControllerMiddleware implements MiddlewareInterface
{

    /**
     * Creates a new controller middleware instance
     *
     * @param class-string $controller The controller class to use
     * @param string $method The method to call on the class during processing of the request
     * @param array<mixed> $vars The variables passed to the method during execution
     * @param Engine|null $templateEngine The template engine to be used for the processing of the request
     */
    public function __construct(private readonly string $controller, private readonly string $method, private readonly array $vars, private readonly Engine|null $templateEngine = null)
    {
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $controller = $this->controller;
        $controller = new $controller();
        if ($controller instanceof Controller) {
            $controller->request = $request;
            $controller->body = $request->getParsedBody();
            $controller->templateEngine = $this->templateEngine;
        }

        return $controller->{$this->method}(...$this->vars);
    }
}