# Routing

Routing in Jinya Router is handled via the `#[Route]` attribute applied to controller methods.

## The Route Attribute

The `#[Route]` attribute allows you to define the HTTP method and the path for an action.

```php
use Jinya\Router\Attributes\Route;
use Jinya\Router\Attributes\HttpMethod;

#[Route(httpMethod: HttpMethod::GET, route: '/my-path')]
public function myAction(): ResponseInterface { ... }
```

### Supported HTTP Methods

The `HttpMethod` enum provides the following values:

- `HttpMethod::GET`
- `HttpMethod::POST`
- `HttpMethod::PUT`
- `HttpMethod::PATCH`
- `HttpMethod::DELETE`
- `HttpMethod::HEAD`

## Path Parameters

You can define dynamic path parameters using the `{name}` syntax. These parameters will be available in the `$this->attributes` array of your controller.

```php
#[Route(httpMethod: HttpMethod::GET, route: '/users/{id}')]
public function getUser(): ResponseInterface
{
    $userId = $this->attributes['id'];
    // ...
}
```

### Regular Expressions in Parameters

You can also use regular expressions to constrain path parameters:

```php
#[Route(httpMethod: HttpMethod::GET, route: '/users/{id:\d+}')]
public function getUserById(): ResponseInterface { ... }
```

## Route Table Generation

Jinya Router scans your controller directory and generates a static routing table using `nikic/fast-route`. This table is cached to improve performance.

When calling `handle_request`, the library automatically checks if the cache is up-to-date or needs to be regenerated based on the files in your controller directory.
