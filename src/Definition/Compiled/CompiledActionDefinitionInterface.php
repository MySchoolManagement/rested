<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\ActionDefinitionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface CompiledActionDefinitionInterface extends ActionDefinitionInterface
{

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @return void
     */
    public function applyAccessControl(AuthorizationCheckerInterface $authorizationChecker);

    /**
     * Gets the generated Url for this action.
     *
     * @param bool $absolute Should the Url be absolute?
     *
     * @return string
     */
    public function getEndpointUrl($absolute = true);

    /**
     * Gets the roles for this action.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleInterface
     */
    public function getRoles();

    /**
     * Gets the route name for the action.
     *
     * @return string
     */
    public function getRouteName();

    /**
     * @return $this
     */
    public function setResourceDefinition(CompiledResourceDefinitionInterface $resourceDefinition);
}
