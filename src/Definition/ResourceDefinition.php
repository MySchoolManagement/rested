<?php
namespace Rested\Definition;

use Rested\AbstractResource;
use Rested\Definition\Parameter;
use Rested\Helper;
use Rested\RequestContext;
use Rested\RestedServiceProvider;
use Rested\Traits\RestedInstanceTrait;

class ResourceDefinition
{

    private $actions = [];

    private $description;

    private $endpoint;

    private $instanceDefinition = null;

    private $name;

    private $resource;

    private $summary;

    public function __construct($name, AbstractResource $resource)
    {
        $this->resource = $resource;
        $this->name = $name;
        $this->restedService = app()->make(RestedServiceProvider::class);
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addCollectionAction($callable)
    {
        return $this->addAction(ActionDefinition::TYPE_COLLECTION, $callable);
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function addInstanceAction($callable, $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE, $callable);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * @return \Rested\Definition\InstanceDefinition
     * @throws \Exception
     */
    public function addInstanceDefinition($class)
    {
        if ($this->instanceDefinition !== null) {
            throw new \Exception('There is already an instance definition attached');
        }

        return ($this->instanceDefinition = InstanceDefinition::create($this, $class));
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    private function addAction($type, $callable)
    {
        return ($this->actions[] = new ActionDefinition($this, $type, $callable));
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

    public function getInstanceDefinition()
    {
        return $this->instanceDefinition;
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

    public static function create($name, AbstractResource $resource)
    {
        return new ResourceDefinition($name, $resource);
    }
}
