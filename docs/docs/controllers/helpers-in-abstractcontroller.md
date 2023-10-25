---
sidebar_position: 3
---

# Helpers in the `AbstractController`

Even though Jinya Router works with any class and method that is annotated with the `Controller` and `Route` attribute
there is an abstract base class that helps you to build your own controllers.

## Request helpers

If Jinya Router detects that a controller class extends the `AbstractController` class it will inject three properties.
Apart from the properties the `AbstractController` provides two helper functions for the request.

### The request itself

First of all, Jinya Router injects the `ServerRequestInterface` that is currently being handled. That means you can
modify it using middlewares and the changed request will be passed down to your controller method.

You can access it using the request property on the controller class.

Check this simple example accessing the server requests attributes:

```php
<?php

namespace App\Controller;

/* ... */

#[Controller]
class ExampleController extends AbstractController
{
    #[Route]
    public function action(): ResponseInterface
    {
        $currentUser = $this->request->getAttribute('currentUser');
        return $this->json($currentUser);
    }
}
```

