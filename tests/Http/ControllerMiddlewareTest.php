<?php

namespace Jinya\Router\Tests\Http;

use ArgumentCountError;
use Error;
use Jinya\Router\Http\ControllerMiddleware;
use Jinya\Router\Tests\Classes\Controller\JsonContentController;
use Jinya\Router\Tests\Classes\Controller\NoContentController;
use Laminas\Stratigility\MiddlewarePipe;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class ControllerMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new ControllerMiddleware(NoContentController::class, 'getAction', ['id' => '1']);
        $result = $middleware->process($request, new MiddlewarePipe());

        self::assertEquals(204, $result->getStatusCode());
    }

    public function testProcessWithReturnData(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new ControllerMiddleware(JsonContentController::class, 'getAction', ['id' => 1]);
        $result = $middleware->process($request, new MiddlewarePipe());

        self::assertEquals(200, $result->getStatusCode());

        $result->getBody()->rewind();
        self::assertJsonStringEqualsJsonString(
            json_encode(['id' => 1], JSON_THROW_ON_ERROR) ?: '',
            $result->getBody()->getContents()
        );
    }

    public function testProcessInputDifferentTypeThanParameter(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new ControllerMiddleware(JsonContentController::class, 'getAction', ['id' => '1']);
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

        $middleware = new ControllerMiddleware(
            NoContentController::class,
            'getAction',
            ['id' => '1', 'username' => 'test']
        );
        try {
            $middleware->process($request, new MiddlewarePipe());
            self::fail('Should throw an error, since username is not a valid parameter');
        } catch (Error $error) {
            self::assertStringContainsString('$username', $error->getMessage());
        }
    }

    public function testProcessParameterMissing(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/no-content');
        $request = $request->withParsedBody([]);

        $middleware = new ControllerMiddleware(NoContentController::class, 'getAction', []);
        try {
            $middleware->process($request, new MiddlewarePipe());
            self::fail('Should throw an ArgumentCountError, since the action requires an ID');
        } catch (ArgumentCountError) {
            self::assertTrue(true);
        }
    }

    public function testProcessControllerDoesNotExist(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/not-existing');
        $request = $request->withParsedBody([]);

        /** @phpstan-ignore-next-line */
        $middleware = new ControllerMiddleware('NonExistingController', 'getAction', ['id' => '1']);
        $result = $middleware->process($request, new MiddlewarePipe());

        self::assertEquals(404, $result->getStatusCode());
    }

    public function testProcessMethodDoesNotExist(): void
    {
        $request = new ServerRequest('GET', 'http://localhost:8080/api/not-existing');
        $request = $request->withParsedBody([]);

        $middleware = new ControllerMiddleware(NoContentController::class, 'findAction', ['id' => '1']);
        $result = $middleware->process($request, new MiddlewarePipe());

        self::assertEquals(404, $result->getStatusCode());
    }
}
