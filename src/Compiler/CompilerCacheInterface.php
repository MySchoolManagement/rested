<?php
namespace Rested\Compiler;

use Rested\Definition\Compiled\CompiledActionDefinitionInterface;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;

interface CompilerCacheInterface
{

    /**
     * @return null|\Rested\Definition\Compiled\CompiledActionDefinitionInterface
     */
    public function findActionByRouteName($routeName);

    /**
     * @param string $routeName The route name to cache the action under.
     * @param \Rested\Definition\Compiled\CompiledActionDefinitionInterface $action The action to cache.
     * @return void
     */
    public function registerAction(
        $routeName,
        CompiledActionDefinitionInterface $action
    );
}
