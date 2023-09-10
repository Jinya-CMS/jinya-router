<?php

namespace Jinya\Router\Tests\Router;

use FastRoute\Dispatcher;
use Jinya\Router\Router\Router;
use Jinya\Router\Tests\Classes\Controller\JsonContentController;
use Jinya\Router\Tests\Classes\Controller\NoContentController;
use Jinya\Router\Tests\Classes\Middleware\AddHeaderMiddleware;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

use function PHPUnit\Framework\assertInstanceOf;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        @unlink(__DIR__ . '/../var/cache/routing/jinya-router.php');
    }

    public function testHandle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/json/5';
        ob_start();
        Router::handle(
            __DIR__ . '/../var/cache',
            __DIR__ . '/../Classes/Controller',
            new Response(404, ['Content-Type' => 'test/not-found'])
        );

        $content = ob_get_clean() ?: '';
        self::assertJsonStringEqualsJsonString(json_encode(['id' => 5], JSON_THROW_ON_ERROR), $content);

        $headers = xdebug_get_headers();
        self::assertNotEmpty($headers);
        self::assertContains('Content-Type: application/json', $headers);
        self::assertContains('TestHeader: X-Test', $headers);
        self::assertContains('TestHeader2: X-Test', $headers);
    }

    public function testHandleNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/not-found';
        ob_start();
        Router::handle(
            __DIR__ . '/../var/cache',
            __DIR__ . '/../Classes/Controller',
            new Response(404, ['Content-Type' => 'test/not-found'])
        );

        $content = ob_get_clean() ?: '';
        self::assertEmpty($content);

        $headers = xdebug_get_headers();
        self::assertNotEmpty($headers);
        self::assertContains('Content-Type: test/not-found', $headers);
    }

    public function testHandleMethodNotAllowed(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/json/5';
        ob_start();
        Router::handle(
            __DIR__ . '/../var/cache',
            __DIR__ . '/../Classes/Controller',
            new Response(404, ['Content-Type' => 'test/not-found'])
        );

        $content = ob_get_clean() ?: '';
        self::assertEmpty($content);

        $headers = xdebug_get_headers();
        self::assertNotEmpty($headers);
        self::assertContains('Allow: GET', $headers);
    }

    public function testBuildRoutingCache(): void
    {
        Router::buildRoutingCache(__DIR__ . '/../var/cache', __DIR__ . '/../Classes/Controller');

        self::assertFileExists(__DIR__ . '/../var/cache/routing/jinya-router.php');

        $routingTableFileContent = file_get_contents(__DIR__ . '/../var/cache/routing/jinya-router.php') ?: '';
        self::assertStringContainsString(
            'return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {',
            $routingTableFileContent
        );

        /** @var Dispatcher $routingTable */
        $routingTable = include __DIR__ . '/../var/cache/routing/jinya-router.php';
        assertInstanceOf(Dispatcher::class, $routingTable);

        $dispatchJsonResult = $routingTable->dispatch('GET', '/json/5');
        self::assertEquals(Dispatcher::FOUND, $dispatchJsonResult[0]);
        self::assertEquals(
            ['ctrl', JsonContentController::class, 'getAction', [new AddHeaderMiddleware('TestHeader2'), new AddHeaderMiddleware()]],
            $dispatchJsonResult[1]
        );

        $dispatchNoContentResult = $routingTable->dispatch('GET', '/no-content');
        self::assertEquals(Dispatcher::FOUND, $dispatchNoContentResult[0]);
        self::assertEquals(
            ['ctrl', NoContentController::class, 'getAction', [new AddHeaderMiddleware('TestHeader2', 2)]],
            $dispatchNoContentResult[1]
        );
    }

    public function testBuildRoutingCacheNonExistingDir(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Router::buildRoutingCache(__DIR__ . '/../var/cache', __DIR__ . '/../Classes/Controllers');
    }

    public function testBuildRoutingCacheNoControllersInDir(): void
    {
        Router::buildRoutingCache(__DIR__ . '/../var/cache', __DIR__ . '/../Classes/Templates');

        /** @var Dispatcher $routingTable */
        $routingTable = include __DIR__ . '/../var/cache/routing/jinya-router.php';
        $dispatchNoContentResult = $routingTable->dispatch('GET', '/no-content');
        self::assertEquals(Dispatcher::NOT_FOUND, $dispatchNoContentResult[0]);
    }
}
