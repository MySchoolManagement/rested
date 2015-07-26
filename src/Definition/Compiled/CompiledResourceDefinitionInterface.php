<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\ResourceDefinitionInterface;

interface CompiledResourceDefinitionInterface extends ResourceDefinitionInterface
{

    /**
     * @param string $routeName
     * @return null|\Rested\Definition\ActionDefinitionInterface
     */
    public function findActionByRouteName($routeName);
}
