# Middleware

Jinya Router supports PSR-15 middlewares, allowing you to intercept and modify requests and responses.

## Applying Middleware

You can apply middlewares to an entire controller class or to individual methods using the `#[Middlewares]` attribute.

```php
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\Middlewares;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Attributes\HttpMethod;

#[Controller]
#[Middlewares(new MyAuthMiddleware())]
class ProtectedController extends AbstractController
{
    #[Route(httpMethod: HttpMethod::GET, route: '/data')]
    public function getData(): ResponseInterface { ... }

    #[Route(httpMethod: HttpMethod::POST, route: '/data')]
    #[Middlewares(new AdditionalCheckMiddleware())]
    public function postData(): ResponseInterface { ... }
}
```

When multiple middlewares are applied, they are executed in the order they are defined. Middlewares applied at the class level are executed before those at the method level.

## Creating Middleware

Any class implementing `Psr\Http\Server\MiddlewareInterface` can be used as a middleware.

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MyAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check authentication
        if (!$request->hasHeader('Authorization')) {
            return new Response(401);
        }

        return $handler->handle($request);
    }
}
```

## Function Middleware

Jinya Router also supports "function middlewares", which are simple functions that act as middlewares.

```php
use Jinya\Router\Attributes\Middlewares;
use Jinya\Router\Http\FunctionMiddleware;

#[Middlewares(new FunctionMiddleware(my_middleware_function(...)))]
```
