<?php

namespace Jinya\Router\Tests\Http;

use Jinya\Router\Http\Controller;
use Jinya\Router\Tests\Classes\Controller\DoingEverythingController;
use Jinya\Router\Tests\Classes\Templates\DoNothingTemplateEngine;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function PHPUnit\Framework\assertEquals;

class ControllerTest extends TestCase
{
    private function getController(): DoingEverythingController
    {
        $request = new ServerRequest('GET', 'http://localhost:8080?id=34', ['Accept' => 'application/json']);

        return new DoingEverythingController($request, [], new DoNothingTemplateEngine());
    }

    public function testRenderAction(): void
    {
        $response = $this->getController()->render('template', []);
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('template#[]', $response->getBody()->getContents());
    }

    public function testRenderActionTemplateEngineNull(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No template engine provided');

        $controller = $this->getController();
        $controller->templateEngine = null;

        $controller->render('template', []);
    }

    public function testRenderActionDifferentResponseStatus(): void
    {
        $response = $this->getController()->render('template', [], Controller::HTTP_BAD_REQUEST);
        $response->getBody()->rewind();

        self::assertEquals(400, $response->getStatusCode());
        self::assertEquals('template#[]', $response->getBody()->getContents());
    }

    public function testGetHeader(): void
    {
        $controller = $this->getController();
        $value = $controller->getHeader('Accept');

        assertEquals('application/json', $value);
    }

    public function testGetHeaderNotExists(): void
    {
        $controller = $this->getController();
        $value = $controller->getHeader('test');

        assertEquals('', $value);
    }

    public function testJsonAction(): void
    {
        $response = $this->getController()->json(['message' => '200']);
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            json_encode(['message' => '200'], JSON_THROW_ON_ERROR),
            $response->getBody()->getContents()
        );
    }

    public function testJsonActionDifferentResponseStatus(): void
    {
        $response = $this->getController()->json(['message' => '400'], Controller::HTTP_BAD_REQUEST);
        $response->getBody()->rewind();

        self::assertEquals(400, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            json_encode(['message' => '400'], JSON_THROW_ON_ERROR),
            $response->getBody()->getContents()
        );
    }

    public function testFileAction(): void
    {
        $response = $this->getController()->file(__FILE__);
        $response->getBody()->rewind();

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals(file_get_contents(__FILE__), $response->getBody()->getContents());
        self::assertEquals('text/x-php', $response->getHeaderLine('Content-Type'));
    }

    public function testFileActionDifferentResponseStatus(): void
    {
        $response = $this->getController()->file(__FILE__, Controller::HTTP_BAD_REQUEST);
        $response->getBody()->rewind();

        self::assertEquals(400, $response->getStatusCode());
        self::assertEquals(file_get_contents(__FILE__), $response->getBody()->getContents());
        self::assertEquals('text/x-php', $response->getHeaderLine('Content-Type'));
    }

    public function testFileActionNotFound(): void
    {
        $response = $this->getController()->file(__FILE__ . 'NotExisting', Controller::HTTP_BAD_REQUEST);
        $response->getBody()->rewind();

        self::assertEquals(404, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());
    }

    public function testNoContentAction(): void
    {
        $response = $this->getController()->noContent();
        $response->getBody()->rewind();

        self::assertEquals(204, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());
    }

    public function testNotFoundAction(): void
    {
        $response = $this->getController()->notFound();
        $response->getBody()->rewind();

        self::assertEquals(404, $response->getStatusCode());
        self::assertEmpty($response->getBody()->getContents());
    }

    public function testNotFoundDataAction(): void
    {
        $response = $this->getController()->notFound(['error' => '404']);
        $response->getBody()->rewind();

        self::assertEquals(404, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            json_encode(['error' => '404'], JSON_THROW_ON_ERROR),
            $response->getBody()->getContents()
        );
    }

    public function testGetQueryParameter(): void
    {
        $controller = $this->getController();
        $value = $controller->getQueryParameter('id');

        assertEquals('34', $value);
    }

    public function testGetQueryParameterNotExists(): void
    {
        $controller = $this->getController();
        $value = $controller->getQueryParameter('test');

        assertEquals('', $value);
    }

    public function testGetQueryParameterNotExistsWithDefault(): void
    {
        $controller = $this->getController();
        $value = $controller->getQueryParameter('test', '5');

        assertEquals('5', $value);
    }
}
