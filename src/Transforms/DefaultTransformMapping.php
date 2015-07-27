<?php
namespace Rested\Transforms;

use Ramsey\Uuid\Uuid;
use Rested\Definition\Filter;
use Rested\Definition\GetterField;
use Rested\Definition\SetterField;

class DefaultTransformMapping implements TransformMappingInterface
{

    /**
     * @var array
     */
    protected $fieldsByOperation = [
        GetterField::OPERATION => [],
        SetterField::OPERATION => [],
    ];

    /**
     * @var \Ramsey\Uuid\string
     */
    protected $compilerId;

    /**
     * @var callable
     */
    protected $fieldFilterCallback = null;

    /**
     * @var \Rested\Definition\Filter[]
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $primaryKeyFieldName = 'uuid';

    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
        $this->compilerId = Uuid::uuid4()->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function addField($name, $dataType, $getter, $setter, $description, $validationParameters = null, $rel = null)
    {
        if ($getter !== null) {
            $this->fieldsByOperation[GetterField::OPERATION][] = new GetterField(
                $name, $getter, $description, $dataType, $rel
            );
        }

        if ($setter !== null) {
            $this->fieldsByOperation[SetterField::OPERATION][] = new SetterField(
                $name, $setter, $description, $dataType, $rel, $validationParameters
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter($name, $dataType, $callable, $description)
    {
        $this->filters[] = new Filter($name, $callable, $description, $dataType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addLink($rel, $routeName)
    {
        $this->links[$rel] = $routeName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilerId()
    {
        return $this->compilerId;
    }

    /**
     * {@inheritdoc}
     */
    public function executeFieldFilterCallback($operation, $instance = null)
    {
        $fields = $this->fieldsByOperation[$operation];

        if ($this->fieldFilterCallback !== null) {
            $args = [$this, $fields, $operation, $instance];
            $fields = call_user_func_array($this->fieldFilterCallback, $args);
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function findField($name, $operation)
    {
        foreach ($this->fieldsByOperation[$operation] as $field) {
            if (strcasecmp($name, $field->getName()) === 0) {
                return $field;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findPrimaryKeyField()
    {
        return $this->findField($this->primaryKeyFieldName, GetterField::OPERATION);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldFilterCallback()
    {
        return $this->fieldFilterCallback;
    }

    /**
     * @return \Rested\Definition\Field[]
     */
    public function getFields($operation)
    {
        return $this->fieldsByOperation[$operation];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKeyFieldName()
    {
        return $this->primaryKeyFieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldFilterCallback($callback)
    {
        $this->fieldFilterCallback = $callback;

        return $this;
    }
}
