<?php
namespace Rested\Definition;

use Rested\Exceptions\ActionExistsException;
use Rested\Security\AccessVoter;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ResourceDefinition
{

    /**
     * @var \Rested\Definition\ActionDefinition[]
     */
    private $actions = [];

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Rested\Definition\TransformMapping
     */
    private $defaultTransformMapping;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $summary;

    /**
     * Constructs a new ResourceDefinition.
     *
     * @param string $name Friendly name for the resource. In the absence of a path, this is converted and used.
     * @param \Rested\Definition\TransformMapping $defaultTransformMapping The default model that is assigned to actions within the resource.
     */
    public function __construct($name, TransformMapping $defaultTransformMapping)
    {
        $this->defaultTransformMapping = $defaultTransformMapping;
        $this->name = $name;
    }

    /**
     * Adds an action to this resource.
     *
     * @param string $type Type of action to add on this resource.
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     *
     * @return \Rested\Definition\ActionDefinition
     * @throws \Rested\Exceptions\ActionExistsException
     */
    protected function addAction($type, $id)
    {
        foreach ($this->actions as $action) {
            if (mb_strtolower($action->getId()) === mb_strtolower($id)) {
                throw new ActionExistsException($id);
            }
        }

        return ($this->actions[] = new ActionDefinition($this, $type, $id));
    }

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
     * @return \Rested\Definition\ActionDefinition
     */
    public function addAffordance($id = 'instance', $method = HttpRequest::METHOD_POST, $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE_AFFORDANCE, $id);
        $action
            ->setMethod($method)
            ->setShouldAppendId(true)
        ;
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * Adds an action to request a collection of resources.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     *
     * @return \Rested\Definition\ActionDefinition
     */
    public function addCollection($id = 'collection')
    {
        return $this->addAction(ActionDefinition::TYPE_COLLECTION, $id);
    }

    /**
     * Adds an action to create a new resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param TransformMapping $transformMapping Transform mapping to use to validate and transform client input.
     *
     * @return \Rested\Definition\ActionDefinition
     */
    public function addCreateAction($id = 'create', TransformMapping $transformMapping = null)
    {
        $action = $this->addAction(ActionDefinition::TYPE_CREATE, $id);
        $action->setTransformMapping($transformMapping);

        return $action;
    }

    /**
     * Adds an action to delete an existing resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param string $type Type of instance ID to accept. The action requires the ID of the resource to delete.
     *
     * @return \Rested\Definition\ActionDefinition
     */
    public function addDeleteAction($id = 'delete', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_DELETE, $id);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * Adds an action to retrieve an existing resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param string $type Type of instance ID to accept. The action requires the ID of the resource to retrieve.
     *
     * @return ActionDefinition
     */
    public function addInstance($id = 'instance', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE, $id);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * Adds an action to update an existing resource.
     *
     * @param string $id Identifier of the action. This is used to construct the route name, action endpoint, etc.
     * @param string $type Type of instance ID to accept. The action requires the ID of the resource to retrieve.
     *
     * @return \Rested\Definition\ActionDefinition
     */
    public function addUpdateAction($id = 'update', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_UPDATE, $id);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * Find all actions of the given type.
     *
     * Note: There can be more than one type of some actions, most notably TYPE_INSTANCE_AFFORDANCE.
     *
     * @param string $type Type of action to search for.
     *
     * @return \Rested\Definition\ActionDefinition[]
     */
    public function findActions($type)
    {
        return array_filter($this->actions,
            function (ActionDefinition $value) use ($type) {
                return ($value->getType() === $type);
            }
        );
    }

    /**
     * @param string $routeName
     * @return null|\Rested\Definition\ActionDefinition
     */
    public function findActionByRouteName($routeName)
    {
        return array_filter($this->actions,
            function (ActionDefinition $value) use ($routeName) {
                return ($value->getRouteName() === $routeName);
            }
        );
    }

    /**
     * Finds the first action of the given type.
     *
     * Note: There can be more than one type of some actions, most notably TYPE_INSTANCE_AFFORDANCE.
     *
     * @param string $type Type of action to search for.
     *
     * @return null|\Rested\Definition\ActionDefinition
     */
    public function findFirstAction($type)
    {
        return (sizeof($actions = $this->findActions($type)) > 0) ? $actions[0] : null;
    }

    /**
     * Gets all of the actions available on the resource.
     *
     * @return \Rested\Definition\ActionDefinition[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    public function getModelClass()
    {
        return $this->getDefaultTransformMapping()->getModelClass();
    }

    /**
     * The default transform mapping that is assigned to actions within the resource.
     *
     * @return \Rested\Definition\TransformMapping
     */
    public function getDefaultTransformMapping()
    {
        return $this->defaultTransformMapping;
    }

    /**
     * Description of the resource.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Friendly name for the resource. In the absence of a path, this is converted and used.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Path that is used when creating the Uri's for the resource.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the short summary of the resource.
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Sets the description of the resource.
     *
     * @param string $value Description of the resource.
     * @return $this
     */
    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    /**
     * Sets the path that will be used when creating the Uri's for the resource.
     *
     * @param string $value Path to the resource.
     * @return $this
     */
    public function setPath($value)
    {
        $this->path = $value;

        return $this;
    }

    /**
     * Sets the short summary of the resource.
     *
     * @param string $value Summary of the resource.
     * @return $this
     */
    public function setSummary($value)
    {
        $this->summary = $value;

        return $this;
    }
}
