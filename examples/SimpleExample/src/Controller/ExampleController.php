<?php

namespace App\Controller;

use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\HttpMethod;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\AbstractController;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class ExampleController extends AbstractController
{
    #[Route(httpMethod: HttpMethod::POST)]
    public function postAction(): ResponseInterface
    {
        return $this->json($this->body);
    }
}