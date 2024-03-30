<?php

namespace Jinya\Router\Http;

use Jinya\Router\Templates\Engine;
use JsonException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
     * @param array<mixed> $vars The variables are passed to the method during execution
     * @param Engine|null $templateEngine The template engine to be used for the processing of the request
     */
    public function __construct(
        private readonly string $controller,
        private readonly string $method,
        private readonly array $vars,
        private readonly Engine|null $templateEngine = null
    ) {
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
            return new Response(404);
        }

        $controller = $this->controller;
        $controller = new $controller();
        if ($controller instanceof AbstractController) {
            $req = $request;
            if (str_starts_with($request->getHeaderLine('Content-Type'), 'application/json')) {
                $request->getBody()->rewind();
                /** @var array<mixed> $decodedBody */
                $decodedBody = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                $req = $req->withParsedBody($decodedBody);
            }
            $controller->body = $req->getParsedBody();
            $controller->request = $req;
            $controller->templateEngine = $this->templateEngine;
        }

        return $controller->{$this->method}(...$this->vars);
    }
}
