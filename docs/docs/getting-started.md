# Getting Started

Follow these steps to get Jinya Router up and running in your project.

## Requirements

- PHP 8.3 or higher
- Composer

## Installation

Install Jinya Router using Composer:

```bash
composer require jinya/router
```

## Project Structure

A typical project structure using Jinya Router might look like this:

```text
.
├── src/
│   └── Controllers/
│       └── MyController.php
├── public/
│   └── index.php
├── cache/
└── composer.json
```

## Basic Setup

In your `public/index.php`, you can set up the request handler:

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Jinya\Router;
use Nyholm\Psr7\Response;

Router\handle_request(
    cacheDirectory: __DIR__ . '/../cache',
    controllerDirectory: __DIR__ . '/../src/Controllers',
    notFoundResponse: new Response(404, [], 'Not Found')
);
```

Ensure that the `cache` directory is writable by the web server. Jinya Router uses this directory to store the generated routing table for optimal performance.
