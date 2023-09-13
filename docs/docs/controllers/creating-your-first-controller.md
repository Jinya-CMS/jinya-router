---
sidebar_position: 1
---

# Creating your first controller

The most simple controller is just a class with a method that returns an empty PSR-7 `ResponseInterface`. Check the
following example.

```php {10,11,13,14} showLineNumbers
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
```

Let's dive into what we see here.

The class `ExampleController` on line 11 is just a simple php class, what makes it special is the attribute in line 10.
This attribute tells Jinya Router to check this class for defined routes. A route is just a simple function, as seen on
line 14. Important is that it returns a PSR-7 `ResponseInterface`. The parameters are also reserved, but we'll talk
about route parameters later. To define this simple function as route for Jinya Router to keep track just add
the `Route` attribute as seen on line 13.

This controller basically does nothing, it only returns a status code of 200 and then exits.