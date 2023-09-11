<?php

namespace Jinya\Router\Tests\Classes\Extension;

use Jinya\Router\Extensions\Extension;
use Jinya\Router\Tests\Classes\Controller\NoContentController;
use PHPUnit\Framework\TestCase;

class SimpleExtension extends Extension
{
    public function beforeGeneration(array $controllers): void
    {
        TestCase::assertContains(NoContentController::class, $controllers);
    }

    public function afterGeneration(string $generatedTable): string
    {
        TestCase::assertStringContainsString(NoContentController::class, $generatedTable);

        return parent::afterGeneration($generatedTable);
    }

    public function additionalRoutes(): string
    {
        $additionalFunction = <<<PHP
<?php
function something(): \Psr\Http\Message\ResponseInterface {
    return new \Nyholm\Psr7\Response(headers: ['Content-Type' => 'application/json']);
}
PHP;
        file_put_contents(__DIR__ . '/../../var/cache/routing/simple-extension.php', $additionalFunction);

        return <<<PHP
include_once __DIR__ . '/simple-extension.php';
\$r->addRoute('GET', '/hello-world', ['fn', 'something', []]);
\$r->addRoute('GET', '/hello-world-middleware', ['fn', 'something', [new \Jinya\Router\Tests\Classes\Middleware\AddHeaderMiddleware()]]);
PHP;
    }
}