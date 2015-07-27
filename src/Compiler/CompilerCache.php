<?php
namespace Rested\Compiler;

use Rested\Definition\Compiled\CompiledActionDefinitionInterface;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;

class CompilerCache implements CompilerCacheInterface
{

    /**
     * @var CompiledActionDefinitionInterface[]
     */
    protected $actionCache = [];

    /**
     * {@inheritdoc}
     */
    public function findActionByRouteName($routeName)
    {
        if (array_key_exists($routeName, $this->actionCache) === true) {
            return $this->actionCache[$routeName];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function registerAction(
        $routeName,
        CompiledActionDefinitionInterface $action)
    {
        $this->actionCache[$routeName] = $action;
    }
}
