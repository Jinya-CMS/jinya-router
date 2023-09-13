<?php

namespace App\Controller;

use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Route;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class ExampleController
{
    #[Route]
    public function action(): ResponseInterface
    {
        return new Response();
    }
}