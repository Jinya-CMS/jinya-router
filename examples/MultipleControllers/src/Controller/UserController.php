<?php

namespace App\Controller;

use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\HttpMethod;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\AbstractController;
use Psr\Http\Message\ResponseInterface;

#[Controller('api/user')]
class UserController extends AbstractController
{
    #[Route]
    public function getUserListAction(): ResponseInterface
    {
        return $this->json([
            [
                'id' => 1,
                'username' => 'john.doe',
                'firstname' => 'John',
                'lastname' => 'Doe',
            ],
            [
                'id' => 2,
                'username' => 'jane.doe',
                'firstname' => 'Jane',
                'lastname' => 'Doe'
            ],
        ]);
    }

    #[Route(route: '{id}')]
    public function getUserByIdAction(int $id): ResponseInterface
    {
        return $this->json([
            'id' => $id,
            'username' => 'jane.doe',
            'firstname' => 'Jane',
            'lastname' => 'Doe'
        ]);
    }

    #[Route(httpMethod: HttpMethod::POST)]
    public function createUserAction(): ResponseInterface
    {
        return $this->json([
            'id' => random_int(0, 100),
            'username' => $this->body['username'],
            'firstname' => $this->body['firstname'],
            'lastname' => $this->body['lastname']
        ], self::HTTP_CREATED);
    }

    #[Route(httpMethod: HttpMethod::PUT, route: '{id}')]
    public function updateUserAction(int $id): ResponseInterface
    {
        // Do the update
        return $this->noContent();
    }

    #[Route(httpMethod: HttpMethod::DELETE, route: '{id}')]
    public function deleteUserAction(int $id): ResponseInterface
    {
        // Do the delete
        return $this->noContent();
    }
}
