<?php

namespace Jinya\Router\Tests\Classes\Templates;

use Jinya\Router\Templates\Engine;

class DoNothingTemplateEngine implements Engine
{
    public function render(string $template, mixed $data = null): string
    {
        return $template . '#' . json_encode($data, JSON_THROW_ON_ERROR);
    }
}
