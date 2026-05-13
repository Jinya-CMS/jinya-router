# Request Handling

Jinya Router provides a convenient `handle_request` function to process incoming HTTP requests and dispatch them to the appropriate controller action.

## The `handle_request` Function

The `handle_request` function is the main entry point for your application. It handles routing, middleware execution, and sending the response.

```php
use Jinya\Router;

Router\handle_request(
    string $cacheDirectory,
    string $controllerDirectory,
    ResponseInterface $notFoundResponse,
    ?Engine $engine = null,
    Extension ...$extensions
): void
```

### Parameters

- `$cacheDirectory`: Path to a writable directory where the generated routing table will be stored.
- `$controllerDirectory`: Path to the directory containing your controller classes.
- `$notFoundResponse`: A PSR-7 `ResponseInterface` to return if no matching route is found.
- `$engine`: (Optional) An implementation of `Jinya\Router\Templates\Engine` for rendering templates.
- `$extensions`: (Optional) One or more `Jinya\Router\Extensions\Extension` instances to extend router behavior.

## Template Engine

You can integrate a template engine by implementing the `Jinya\Router\Templates\Engine` interface.

```php
use Jinya\Router\Templates\Engine;

class MyTemplateEngine implements Engine
{
    public function render(string $template, mixed $data = null): string
    {
        // Your template rendering logic
    }
}
```

Once passed to `handle_request`, you can use `$this->render()` in your controllers.

## Extensions

Extensions allow you to hook into the routing table generation process.

```php
use Jinya\Router\Extensions\Extension;

class MyExtension extends Extension
{
    public function beforeGeneration(array $controllers): void { ... }
    public function afterGeneration(string $generatedTable): string { ... }
    public function additionalRoutes(): string { ... }
    public function recreateCache(): bool { ... }
}
```
