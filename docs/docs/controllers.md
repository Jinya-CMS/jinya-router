# Controllers

In Jinya Router, controllers are classes that group related request handling logic. They are marked with the `#[Controller]` attribute.

## Defining a Controller

To create a controller, simply add the `#[Controller]` attribute to your class. It is recommended to extend `Jinya\Router\Http\AbstractController` to gain access to helpful utility methods.

```php
use Jinya\Router\Attributes\Controller;
use Jinya\Router\Http\AbstractController;

#[Controller]
class MyController extends AbstractController
{
    // ... actions
}
```

### Base Path

The `#[Controller]` attribute can take an optional `urlPrefix` parameter, which will be prepended to all routes defined within that controller.

```php
#[Controller(urlPrefix: '/api/v1')]
class ApiController extends AbstractController
{
    // Routes here will be prefixed with /api/v1
}
```

## The AbstractController

The `AbstractController` provides several properties and methods to simplify request handling:

### Properties

- `$this->request`: The PSR-7 `ServerRequestInterface` instance.
- `$this->body`: The parsed request body.
- `$this->attributes`: The request attributes (e.g., route parameters).

### Utility Methods

- `getHeader(string $name)`: Gets a request header value.
- `getQueryParameter(string $name, string $default)`: Gets a query parameter value.
- `json(mixed $data, int $status = 200)`: Returns a JSON response.
- `noContent()`: Returns a 204 No Content response.
- `file(string $filename, int $status = 200)`: Returns a file response.
- `render(string $template, mixed $data, int $status = 200)`: Renders a template (if a template engine is configured).
- `notFound(mixed $data)`: Returns a 404 Not Found response.
