<?php

namespace Jinya\Router\Tests\Classes\Controller;

use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\Controller as BaseController;
use Psr\Http\Message\ResponseInterface;

#[Controller('no-content')]
class NoContentController extends BaseController
{
    #[Route]
    public function getAction(string $id): ResponseInterface
    {
        return $this->noContent();
    }
}
