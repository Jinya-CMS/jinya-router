<?php

namespace Jinya\Router\Templates;

/**
 * Implement this interface to create a template engine usable by the base controller class
 */
interface Engine
{
    /**
     * Renders the given template with the given data
     *
     * @param string $template The template to render. This must be a path
     * @param mixed|null $data The data that is passed into the template
     * @return string
     */
    public function render(string $template, mixed $data = null): string;
}