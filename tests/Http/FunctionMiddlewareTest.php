<?php

namespace Jinya\Router\Tests\Http;

use ArgumentCountError;
use Error;
use Jinya\Router\Http\FunctionMiddleware;
use Laminas\Stratigility\MiddlewarePipe;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class FunctionMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new FunctionMiddleware('test_action_no_vars', []);
        $result = $middleware->process($request, new MiddlewarePipe());

        self::assertEquals(204, $result->getStatusCode());
    }

    public function testProcessInputDifferentTypeThanParameter(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new FunctionMiddleware('test_action_with_vars_and_body', ['id' => '1']);
        $result = $middleware->process($request, new MiddlewarePipe());

        self::assertEquals(200, $result->getStatusCode());

        $result->getBody()->rewind();
        self::assertJsonStringEqualsJsonString(
            json_encode(['id' => 1], JSON_THROW_ON_ERROR) ?: '',
            $result->getBody()->getContents()
        );
    }

    public function testProcessTooManyParameters(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new FunctionMiddleware('test_action_no_vars', ['id' => 1]);
        try {
            $middleware->process($request, new MiddlewarePipe());
            self::fail('Should throw an error, since id is not a valid parameter');
        } catch (Error $error) {
            self::assertStringContainsString('$id', $error->getMessage());
        }
    }

    public function testProcessParameterMissing(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new FunctionMiddleware('test_action_with_vars_and_body', []);
        try {
            $middleware->process($request, new MiddlewarePipe());
            self::fail('Should throw an ArgumentCountError, since the action requires an ID');
        } catch (ArgumentCountError) {
            self::assertTrue(true);
        }
    }

    public function testProcessMethodDoesNotExist(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/not-existing');
        $request = $request->withParsedBody([]);

        $middleware = new FunctionMiddleware('test_action_that_does_not_exist', []);
        $result = $middleware->process($request, new MiddlewarePipe());

        self::assertEquals(404, $result->getStatusCode());
    }
}
