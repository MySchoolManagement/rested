<?php
namespace Rested\Definition;

use Rested\AbstractResource;
use Rested\Definition\Parameter;
use Rested\Exceptions\ActionExistsException;
use Rested\Helper;
use Rested\RequestContext;
use Rested\RestedServiceProvider;
use Rested\Security\AccessVoter;
use Rested\Traits\RestedInstanceTrait;

class ResourceDefinition
{

    private $actions = [];

    private $description;

    private $endpoint;

    private $model = null;

    private $name;

    private $resource;

    private $summary;

    public function __construct($name, AbstractResource $resource, $class)
    {
        $this->resource = $resource;
        $this->model = Model::create($this, $class);
        $this->name = $name;
        $this->restedService = app()->make(RestedServiceProvider::class);
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addCollectionAction($name = 'collection', $callable = 'collection')
    {
        return $this->addAction(ActionDefinition::TYPE_COLLECTION, $name, $callable);
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addCreateAction($name = 'create', $callable = 'create', Model $modelOverride = null)
    {
        $action = $this->addAction(ActionDefinition::TYPE_CREATE, $name, $callable);
        $action->setModelOverride($modelOverride);

        return $action;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addDeleteAction($name = 'delete', $callable = 'delete', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_DELETE, $name, $callable);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addInstanceAction($name = 'instance', $callable = 'instance', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE, $name, $callable);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     * @throws \Rested\Exceptions\ActionExistsException
     */
    private function addAction($type, $name, $callable)
    {
        foreach ($this->actions as $action) {
            if (mb_strtolower($action->getName()) === mb_strtolower($name)) {
                throw new ActionExistsException($name);
            }
        }
        return ($this->actions[] = new ActionDefinition($this, $type, $name, $callable));
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addUpdateAction($name = 'update', $callable = 'update', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_UPDATE, $name, $callable);
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

    public function getUrl()
    {
        return Helper::makeUrl($this->restedService->getPrefix(), $this->endpoint);
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

    public static function create($name, AbstractResource $resource, $class)
    {
        return new ResourceDefinition($name, $resource, $class);
    }
}
