<?php

namespace Jinya\Router\Tests\Classes\Controller;

use Jinya\Router\Http\AbstractController;
use Jinya\Router\Tests\Classes\Middleware\AddHeaderMiddleware;
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Middlewares;
use Jinya\Router\Attributes\Route;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class JsonContentController extends AbstractController
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
