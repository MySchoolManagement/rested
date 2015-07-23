<?php
namespace Rested;

use Rested\Definition\ResourceDefinition;

interface RestedResourceInterface
{

    /**
     * Gets the service for checking access rights.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    public function getAuthorizationChecker();

    /**
     * Gets the context bound to the current request.
     *
     * @return \Rested\ImmutableContext
     */
    public function getCurrentContext();

    /**
     * Gets the current request.
     *
     * Note: This is stacked and can change as internal API calls are executed.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getCurrentRequest();

    /**
     * Gets the transform mapping bound to the current context.
     *
     * @return \Rested\Definition\TransformMapping
     */
    public function getCurrentTransformMapping();

    /**
     * Gets the definition for this resource.
     *
     * @return mixed
     */
    public function getDefinition();

    /**
     * Gets the factory for creating things.
     *
     * @return \Rested\FactoryInterface
     */
    public function getFactory();

    /**
     * Gets the Rested service.
     *
     * @return \Rested\RestedServiceInterface
     */
    public function getRestedService();

    /**
     * Gets the current user.
     *
     * @return null|\App\User
     */
    public function getUser();
}
