<?php

namespace App\Controller;

use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\AbstractController;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class AdminController extends AbstractController
{
    #[Route(route: '{route:.*}')]
    public function indexAction(string $route): ResponseInterface
    {
        if (is_file(__DIR__ . '/' . $route)) {
            return $this->file(__DIR__ . '/' . $route);
        }

        return $this->file(__DIR__ . '/index.html');
    }
}
