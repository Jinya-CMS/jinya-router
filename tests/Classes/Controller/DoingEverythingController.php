<?php

namespace Jinya\Router\Tests\Classes\Controller;

use Jinya\Router\Http\Controller as BaseController;
use Jinya\Router\Templates\Engine;
use Psr\Http\Message\ServerRequestInterface;

class DoingEverythingController extends BaseController
{

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