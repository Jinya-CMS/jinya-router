<?php

namespace Jinya\Router\Tests\Classes\Controller;

use Jinya\Router\Http\AbstractController;
use Jinya\Router\Tests\Classes\Middleware\AddHeaderMiddleware;
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Middlewares;
use Jinya\Router\Attributes\Route;
use Psr\Http\Message\ResponseInterface;

#[Controller('no-content')]
class NoContentController extends AbstractController
{
    #[Route]
    #[Middlewares(new AddHeaderMiddleware('TestHeader2', 2))]
    public function getAction(string $id): ResponseInterface
    {
        return $this->noContent();
    }
}
