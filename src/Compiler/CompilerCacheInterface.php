<?php
namespace Rested\Compiler;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface CompilerCacheInterface
{

    /**
     * Finds the action with the given route name.
     *
     * If an autorization interface is provided then it is expected behaviour for this to trigger the application of
     * security rules on the entire resource definition.
     *
     * This should be refactored at some point as this is not necessarily desired behaviour. This should also allow for
     * a seperate cache per security token for use-cases using sub-queries and user impersonation.
     *
     * @return null|\Rested\Definition\Compiled\CompiledResourceDefinitionInterface
     */
    public function findResourceDefinition($routeName, AuthorizationCheckerInterface $authorizationChecker = null);

    /**
     * @param string $routeName The route name to cache the definition under.
     * @param \Rested\Definition\Compiled\CompiledResourceDefinitionInterface $resourceDefinition The resource definition to cache.
     * @return void
     */
    public function registerResourceDefinition(
        $routeName,
        CompiledResourceDefinitionInterface $resourceDefinition
    );
}
