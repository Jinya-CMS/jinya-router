<?php

namespace Jinya\Router\Tests\Classes\Controller;

use Jinya\Router\Http\AbstractController;
use Jinya\Router\Templates\Engine;
use Psr\Http\Message\ServerRequestInterface;

class DoingEverythingController extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     * @param array<string, mixed>|object|null $body
     * @param Engine|null $templateEngine
     */
    public function __construct(
        ServerRequestInterface $request,
        array|object|null $body,
        Engine|null $templateEngine = null
    ) {
        $this->request = $request;
        $this->body = $body;
        $this->templateEngine = $templateEngine;
    }
}
