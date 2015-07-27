<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\ResourceDefinition;
use Rested\FactoryInterface;
use Rested\Transforms\CompiledTransformMappingInterface;
use Rested\Transforms\TransformInterface;

class CompiledResourceDefinition extends ResourceDefinition implements CompiledResourceDefinitionInterface
{

    public function __construct(
        FactoryInterface $factory,
        $path, $name, $summary, $description,
        array $actions = [],
        TransformInterface $defaultTransform,
        CompiledTransformMappingInterface $defaultTransformMapping
    )
    {
        $this->actions = $actions;
        $this->defaultTransform = $defaultTransform;
        $this->defaultTransformMapping = $defaultTransformMapping;
        $this->description = $description;
        $this->factory = $factory;
        $this->name = $name;
        $this->path = $path;
        $this->summary = $summary;

        foreach ($actions as $action) {
            $action->setResourceDefinition($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findActionByRouteName($routeName)
    {
        foreach ($this->actions as $action) {
            if ((is_a($action, CompiledActionDefinitionInterface::class) === true)
                && ($action->getRouteName() === $routeName)) {
                return $action;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelClass()
    {
        return $this->getDefaultTransformMapping()->getModelClass();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTransformMapping()
    {
        return $this->defaultTransformMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return $this->summary;
    }
}
