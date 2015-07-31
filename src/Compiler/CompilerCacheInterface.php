<?php
namespace Rested\Compiler;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface CompilerCacheInterface
{

    /**
     * Finds the resource definition that manages the given route name.
     *
     * When in the request scope then it is expected behaviour for this to trigger the application of security rules on
     * the entire resource definition.
     *
     * This should be refactored at some point as this is not necessarily desired behaviour. This should also allow for
     * a seperate cache per security token for use-cases using sub-queries and user impersonation.
     *
     * @return null|\Rested\Definition\Compiled\CompiledResourceDefinitionInterface
     */
    public function findResourceDefinition($routeName);

    /**
     * Finds the resource definition that manages the given model.
     *
     * @param string $modelClass
     * @return null|\Rested\Definition\Compiled\CompiledResourceDefinitionInterface
     */
    public function findResourceDefinitionForModelClass($modelClass);

    /**
     * @param string $routeName The route name to cache the definition under.
     * @param \Rested\Definition\Compiled\CompiledResourceDefinitionInterface $resourceDefinition The resource definition to cache.
     * @return void
     */
    public function registerResourceDefinition(
        $routeName,
        CompiledResourceDefinitionInterface $resourceDefinition
    );

    /**
     * Serializes the definitions for caching.
     *
     * @return mixed
     */
    public function serialize();

    /**
     * Sets the authorization checker for applying access controls to resource definitions.
     *
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @return void
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker);
}
