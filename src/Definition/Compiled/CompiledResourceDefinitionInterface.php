<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\ResourceDefinitionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface CompiledResourceDefinitionInterface extends ResourceDefinitionInterface
{

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @return void
     */
    public function applyAccessControl(AuthorizationCheckerInterface $authorizationChecker);

    /**
     * @param string $routeName
     * @return null|\Rested\Definition\ActionDefinitionInterface
     */
    public function findActionByRouteName($routeName);
}
