# Introduction

Jinya Router is a simple, capable, and fast attribute-based routing library for PHP. It leverages PHP 8 attributes to define routes directly in your controllers, making your routing logic easy to read and maintain.

## Key Features

- **Attribute-based routing**: Define routes where they belong—directly on your controller methods.
- **Fast and Efficient**: Built on top of `nikic/fast-route`, ensuring high-performance routing.
- **PSR-7 & PSR-15 Compatible**: Full support for PSR-7 HTTP messages and PSR-15 middlewares.
- **Middleware Support**: Easily apply middlewares to controllers or individual actions.
- **Simple Integration**: Easy to set up and integrate into any PHP project.

## Basic Example

Here is a quick look at how Jinya Router looks in action:

```php
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\HttpMethod;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\AbstractController;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class MyController extends AbstractController
{
    #[Route(httpMethod: HttpMethod::GET, route: '/hello')]
    public function helloAction(): ResponseInterface
    {
        return $this->json(['message' => 'Hello, World!']);
    }
}
```

And to handle the request:

```php
use Jinya\Router;

Router\handle_request(
    cacheDirectory: __DIR__ . '/cache',
    controllerDirectory: __DIR__ . '/src/Controllers',
    notFoundResponse: new MyCustomNotFoundResponse()
);
```
