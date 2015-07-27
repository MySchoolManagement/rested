<?php
namespace Rested\Compiler;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompilerCache implements CompilerCacheInterface
{

    /**
     * @var CompiledResourceDefinitionInterface[]
     */
    protected $resourceDefinitions = [];

    /**
     * {@inheritdoc}
     */
    public function findResourceDefinition($routeName, AuthorizationCheckerInterface $authorizationChecker = null)
    {
        $resourceDefinition = null;

        if (array_key_exists($routeName, $this->resourceDefinitions) === true) {
            $resourceDefinition = $this->resourceDefinitions[$routeName];
        }

        if (($authorizationChecker !== null) && ($resourceDefinition !== null)) {
            $resourceDefinition->applyAccessControl($authorizationChecker);
        }

        return $resourceDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function registerResourceDefinition(
        $routeName,
        CompiledResourceDefinitionInterface $resourceDefinition)
    {
        $this->resourceDefinitions[$routeName] = $resourceDefinition;
    }
}
