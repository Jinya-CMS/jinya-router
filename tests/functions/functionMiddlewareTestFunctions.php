<?php

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;

function test_action_no_vars(): ResponseInterface
{
    return new Response(204);
}

function test_action_with_vars_and_body(int $id): ResponseInterface
{
    return (new Response(200))->withBody(Stream::create(json_encode(['id' => $id], JSON_THROW_ON_ERROR)));
}
