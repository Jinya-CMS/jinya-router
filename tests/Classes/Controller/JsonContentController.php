<?php

namespace Jinya\Router\Tests\Classes\Controller;

use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\Controller as BaseController;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class JsonContentController extends BaseController
{
    #[Route(route: 'json/{id}')]
    public function getAction(int $id): ResponseInterface
    {
        return $this->json([
            'id' => $id
        ]);
    }
}
