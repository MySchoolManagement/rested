<?php
namespace Rested;

use Symfony\Component\HttpFoundation\Request;

interface RestedResourceInterface
{

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public function createDefinition();

    /**
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    public function getAuthorizationChecker();

    /**
     * @return \Rested\RequestContext
     */
    public function getCurrentContext();

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getCurrentRequest();

    /**
     * @return \Rested\FactoryInterface
     */
    public function getFactory();
}
