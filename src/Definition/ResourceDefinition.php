<?php
namespace Rested\Definition;

use Rested\Definition\Parameter;
use Rested\Exceptions\ActionExistsException;
use Rested\Helper;
use Rested\RequestContext;
use Rested\RestedResourceInterface;
use Rested\RestedServiceInterface;
use Rested\RestedServiceProvider;
use Rested\Security\AccessVoter;
use Symfony\Component\HttpFoundation\Request;

class ResourceDefinition
{

    private $actions = [];

    private $description;

    private $endpoint;

    private $model = null;

    private $name;

    private $resource;

    private $summary;

    public function __construct($name, RestedResourceInterface $resource, RestedServiceInterface $restedService, $class)
    {
        $this->resource = $resource;
        $this->model = $this->resource->getFactory()->createModel($this, $class);
        $this->name = $name;
        $this->restedService = $restedService;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addCollection($name = 'collection')
    {
        return $this->addAction(ActionDefinition::TYPE_COLLECTION, $name);
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addCreateAction($name = 'create', Model $modelOverride = null)
    {
        $action = $this->addAction(ActionDefinition::TYPE_CREATE, $name);
        $action->setModelOverride($modelOverride);

        return $action;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addDeleteAction($name = 'delete', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_DELETE, $name);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addInstance($name = 'instance', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE, $name);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addInstanceAction($name = 'instance', $method = Request::METHOD_POST, $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE_ACTION, $name);
        $action->setMethod($method);
        $action->addToken('id', $type);
        $action->appendUrl($name);

        return $action;
    }


    /**
     * @return \Rested\Definition\ActionDefinition
     * @throws \Rested\Exceptions\ActionExistsException
     */
    private function addAction($type, $name)
    {
        foreach ($this->actions as $action) {
            if (mb_strtolower($action->getName()) === mb_strtolower($name)) {
                throw new ActionExistsException($name);
            }
        }
        return ($this->actions[] = new ActionDefinition($this, $type, $name));
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addUpdateAction($name = 'update', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_UPDATE, $name);
        $action->addToken('id', $type);

        return $action;
    }

    public function filterActionsForAccess()
    {
        $user = $this->getResource()->getUser();

        return array_filter($this->actions, function($action) use ($user) {
            return $user->isGranted(AccessVoter::ATTRIB_ACTION_ACCESS, $action);
        });
    }

    /**
     * @return \Rested\Definition\ActionDefinition|null
     */
    public function findAction($type)
    {
        foreach ($this->actions as $action) {
            if ($action->getType() === $type) {
                return $action;
            }
        }

        return null;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getUrl($path = '')
    {
        return Helper::makeUrl($this->restedService->getPrefix(), $this->endpoint, $path);
    }

    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    public function setEndpoint($value)
    {
        $this->endpoint = $value;

        return $this;
    }

    public function setSummary($value)
    {
        $this->summary = $value;

        return $this;
    }
}
