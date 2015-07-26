<?php
namespace Rested\Compiler;

use Rested\Definition\ActionDefinitionInterface;
use Rested\Definition\ResourceDefinitionInterface;

interface CompilerInterface
{

    /**
     * @return \Rested\Definition\Compiled\CompiledResourceDefinitionInterface
     */
    public function compile(ResourceDefinitionInterface $resourceDefinition);
}
