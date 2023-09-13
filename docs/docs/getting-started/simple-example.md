---
sidebar_position: 2
---

# Simple example

Jinya Router needs at least one controller to actually do some routing, let's see how it works.

## The controller

The most basic controller can be found below. Jinya Router includes a base class for your controllers allowing access to
the request, the parsed body and the headers.

In the example below, we create a simple echo server, you post something, and the same value is returned as JSON.

```php title="src/App/Controller/ExampleController.php"
<?php

namespace App\Controller;

use Jinya\Router\Attributes\Controller;
use Jinya\Router\Attributes\HttpMethod;
use Jinya\Router\Attributes\Middlewares;
use Jinya\Router\Attributes\Route;
use Jinya\Router\Http\AbstractController;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class JsonContentController extends AbstractController
{
    #[Route(httpMethod: HttpMethod::POST)]
    public function postAction(): ResponseInterface
    {
        return $this->json($this->body);
    }
}
```

Apart from the controller class, we also need an index.php file. In the index.php file, we simply call Jinya Router and
tell it to perform the actual routing and request handling.

```php title="public/index.php"
<?php

require __DIR__ . '/../vendor/autoload.php';

Jinya\Router\handle_request(
    __DIR__ . '/../var/cache',
    __DIR__ . '/../src/Controller',
    new Nyholm\Psr7\Response(404, ['Content-Type' => 'text/plain'], 'Path not found')
);
```

And this is basically it, from here you can start your first app.
