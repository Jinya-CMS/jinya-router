<?php

namespace Jinya\Router\Extensions;

/**
 * Base class for Jinya Router extensions. With extensions, it is possible to extend the behavior and the generation of the routing table
 */
abstract class Extension
{

    /**
     * Gets executed before the table is generated. This function allows inspecting the available controllers
     *
     * @param class-string[] $controllers An array of controllers used by the table generator
     * @return void
     */
    public function beforeGeneration(array $controllers): void
    {
    }

    /**
     * Gets executed after the routing table has been generated. The generated routing table is passed to the function as argument. The function returns the modified routing table or the original one if no changes were made
     *
     * @param string $generatedTable The generated routing table
     * @return string
     */
    public function afterGeneration(string $generatedTable): string
    {
        return $generatedTable;
    }

    /**
     * Allows the injection of additional routes into the routing table
     *
     * @return string
     */
    public function additionalRoutes(): string
    {
        return '';
    }
}
