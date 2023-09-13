<?php

require __DIR__ . '/../vendor/autoload.php';

Jinya\Router\handle_request(
    __DIR__ . '/../var/cache',
    __DIR__ . '/../src/Controller',
    new Nyholm\Psr7\Response(404, ['Content-Type' => 'text/plain'], 'Path not found')
);