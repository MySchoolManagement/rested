<?php
namespace Rested\Definition;

use Rested\Exceptions\ActionExistsException;
use Rested\FactoryInterface;
use Rested\Transforms\TransformInterface;
use Rested\Transforms\TransformMappingInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ResourceDefinition implements ResourceDefinitionInterface
{

    /**
     * @var \Rested\Definition\ActionDefinitionInterface[]
     */
    protected $actions = [];

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \Rested\Transforms\TransformInterface
     */
    protected $defaultTransform;

    /**
     * @var \Rested\Transforms\TransformMappingInterface
     */
    protected $defaultTransformMapping;

    /**
     * @var \Rested\FactoryInterface
     */
    protected $factory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $summary;

    /**
     * Constructs a new ResourceDefinition.
     *
     * @param string $name Friendly name for the resource. In the absence of a path, this is converted and used.
     * @param \Rested\Transforms\Transforminterface $defaultTansform The default transform that is assigned ot actions.
     * @param \Rested\Transforms\TransformMappingInterface $defaultTransformMapping The default model that is assigned to actions.
     */
    public function __construct(FactoryInterface $factory, $name, TransformInterface $defaultTansform, TransformMappingInterface $defaultTransformMapping)
    {
        $this->defaultTransform = $defaultTansform;
        $this->defaultTransformMapping = $defaultTransformMapping;
        $this->factory = $factory;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    protected function addAction($type, $id)
    {
        foreach ($this->actions as $action) {
            if (mb_strtolower($action->getId()) === mb_strtolower($id)) {
                throw new ActionExistsException($id);
            }
        }

        // FIXME: should use a factory
        return ($this->actions[] = new ActionDefinition($this->defaultTransform, $this->defaultTransformMapping, $type, $id));
    }

    /**
     * {@inheritdoc}
     */
    public function addAffordance($id = 'instance', $method = HttpRequest::METHOD_POST, $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE_AFFORDANCE, $id);
        $action
            ->setHttpMethod($method)
            ->setShouldAppendId(true)
        ;
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    public function addCollection($id = 'collection')
    {
        return $this->addAction(ActionDefinition::TYPE_COLLECTION, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function addCreateAction($id = 'create')
    {
        $action = $this->addAction(ActionDefinition::TYPE_CREATE, $id);

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    public function addDeleteAction($id = 'delete', $type = Parameter::TYPE_UUID)
    {
        $modelClass = $this->getDefaultTransformMapping()->getModelClass();
        $emptyTransformMapping = $this->factory->createTransformMapping($modelClass);

        $action = $this->addAction(ActionDefinition::TYPE_DELETE, $id);
        $action->addToken('id', $type);
        $action->setTransformMapping($emptyTransformMapping);

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    public function addInstance($id = 'instance', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_INSTANCE, $id);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    public function addUpdateAction($id = 'update', $type = Parameter::TYPE_UUID)
    {
        $action = $this->addAction(ActionDefinition::TYPE_UPDATE, $id);
        $action->addToken('id', $type);

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    public function findActions($type)
    {
        return array_filter(
            $this->actions,
            function (ActionDefinition $value) use ($type) {
                return ($value->getType() === $type);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findFirstAction($type)
    {
        $actions = $this->findActions($type);

        if (sizeof($actions) > 0) {
            return array_shift($actions);
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
    public function getDefaultTransform()
    {
        return $this->defaultTransform;
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

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($value)
    {
        $this->path = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSummary($value)
    {
        $this->summary = $value;

        return $this;
    }
}
