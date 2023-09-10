<?php

namespace Jinya\Router\Tests\Classes\Controller;

use Jinya\Router\Tests\Classes\Middleware\AddHeaderMiddleware;
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Middlewares;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\Controller as BaseController;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class JsonContentController extends BaseController
{
    #[Route(route: 'json/{id}')]
    #[Middlewares(new AddHeaderMiddleware('TestHeader2'), new AddHeaderMiddleware())]
    public function getAction(int $id): ResponseInterface
    {
        return $this->json([
            'id' => $id
        ]);
    }
}
