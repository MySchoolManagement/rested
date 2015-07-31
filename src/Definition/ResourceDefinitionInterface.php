<?php
namespace Rested\Definition;

use Rested\Transforms\TransformMappingInterface;
use Symfony\Component\HttpFoundation\Request;

interface ResourceDefinitionInterface
{

    /**
     * Adds an affordance (ability) that can be applied to a resource.
     *
     * Affordances allow you to provide logical endpoints to transition the resource between various states.
     *
     * A popular example of an affordance is a bank account. Your account has a balance and the ability to perform
     * actions on that balance (withdrawals, deposits, etc.).
     *
     * Through your API you may provide these affordances as endpoints below the base URL of the resource
     * (e.g. http://api.x.com/account/1/withdraw). These actions can affect the state of the underlying account. If your
     * account goes overdrawn then you can remove the ability to access the withdraw affordance as the account is not
     * in a positive balance.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param string $method HTTP verb the client needs to use to access the affordance.
     * @param string $type Type of instance ID to accept. An affordance needs an existing resource to operate on.
     *
     * @return \Rested\Definition\ActionDefinitionInterface
     */
    public function addAffordance($id = 'instance', $method = Request::METHOD_POST, $type = Parameter::TYPE_UUID);

    /**
     * Adds an action to request a collection of resources.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     *
     * @return \Rested\Definition\ActionDefinitionInterface
     */
    public function addCollection($id = 'collection');

    /**
     * Adds an action to create a new resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     *
     * @return \Rested\Definition\ActionDefinitionInterface
     */
    public function addCreateAction($id = 'create');

    /**
     * Adds an action to delete an existing resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param string $type Type of instance ID to accept. The action requires the ID of the resource to delete.
     *
     * @return \Rested\Definition\ActionDefinitionInterface
     */
    public function addDeleteAction($id = 'delete', $type = Parameter::TYPE_UUID);

    /**
     * Adds an action to retrieve an existing resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param string $type Type of instance ID to accept. The action requires the ID of the resource to retrieve.
     *
     * @return \Rested\Definition\ActionDefinitionInterface
     */
    public function addInstance($id = 'instance', $type = Parameter::TYPE_UUID);

    /**
     * Adds an action to update an existing resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param string $type Type of instance ID to accept. The action requires the ID of the resource to retrieve.
     *
     * @return \Rested\Definition\ActionDefinitionInterface
     */
    public function addUpdateAction($id = 'update', $type = Parameter::TYPE_UUID);

    /**
     * Find all actions of the given type.
     *
     * Note: There can be more than one type of some actions, most notably TYPE_INSTANCE_AFFORDANCE.
     *
     * @param string $type Type of action to search for.
     *
     * @return \Rested\Definition\ActionDefinitionInterface[]
     */
    public function findActions($type);

    /**
     * Finds the first action of the given type.
     *
     * Note: There can be more than one type of some actions, most notably TYPE_INSTANCE_AFFORDANCE.
     *
     * @param string $type Type of action to search for.
     *
     * @return null|\Rested\Definition\ActionDefinitionInterface
     */
    public function findFirstAction($type);

    /**
     * Gets all of the actions available on the resource.
     *
     * @return \Rested\Definition\ActionDefinitionInterface[]
     */
    public function getActions();

    /**
     * Gets the class the controller is defined in.
     *
     * @return string
     */
    public function getControllerClass();

    /**
     * Classname of the data model backing the resource.
     *
     * @return string
     */
    public function getModelClass();

    /**
     * The default transform that is assigned to actions within the resource.
     *
     * @return \Rested\Transforms\TransformInterface
     */
    public function getDefaultTransform();

    /**
     * The default transform mapping that is assigned to actions within the resource.
     *
     * @return \Rested\Transforms\TransformMappingInterface
     */
    public function getDefaultTransformMapping();

    /**
     * Description of the resource.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Friendly name for the resource. In the absence of a path, this is converted and used.
     *
     * @return string
     */
    public function getName();

    /**
     * Path that is used when creating the Uri's for the resource.
     *
     * @return string
     */
    public function getPath();

    /**
     * Gets the short summary of the resource.
     *
     * @return string
     */
    public function getSummary();

    /**
     * Sets the description of the resource.
     *
     * @param string $value Description of the resource.
     * @return $this
     */
    public function setDescription($value);

    /**
     * Sets the path that will be used when creating the Uri's for the resource.
     *
     * @param string $value Path to the resource.
     * @return $this
     */
    public function setPath($value);

    /**
     * Sets the short summary of the resource.
     *
     * @param string $value Summary of the resource.
     * @return $this
     */
    public function setSummary($value);
}
