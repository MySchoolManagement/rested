<?php
namespace Rested\Definition;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class TransformMapping
{

    protected $modelClass;

    protected $fields = [];

    protected $filters = [];

    protected $links = [];

    protected $primaryKeyField = 'uuid';

    protected $customFieldFilter = null;

    /**
     * Constructor
     *
     * @param string $modelClass
     */
    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Adds a new field to the mapping.
     *
     * @param string $name
     *            The name of field. Used in the representation sent to the client.
     * @param string $type
     *            The data type of the field.
     * @param callable $getter
     *            Method to call on the resource to get the value.
     * @param callable $setter
     *            When applying a model, can this field be changed?
     * @param string $description
     *            Description of this field.
     * @param array $validationParameters
     *            List of validation settings.  These are symfony form settings.
     *
     * @return \Rested\Definition\InstanceDefinition Self
     */
    public function addField($name, $dataType, $getter, $setter, $description, $validationParameters = null, $rel = null)
    {
        $this->fields[] = new Field($name, $getter, $setter, $description, $dataType, $validationParameters, $rel);

        return $this;
    }

    public function addFilter($name, $dataType, $callable, $description)
    {
        $this->filters[] = new Filter($name, $callable, $description, $dataType);

        return $this;
    }

    public function addLink($routeName, $rel)
    {
        $this->links[$rel] = $routeName;

        return $this;
    }

    public function findField($name)
    {
        foreach ($this->fields as $field) {
            if (strcasecmp($name, $field->getName()) === 0) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return \Rested\Definition\Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return \Rested\Definition\Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return \Rested\Definition\Field|null
     */
    public function getPrimaryKeyField()
    {
        return $this->findField($this->primaryKeyField);
    }

    public function getPrimaryKeyValueForInstance($instance)
    {
        if (($field = $this->getPrimaryKeyField()) !== null) {
            $isEloquent = $instance instanceof EloquentModel;

            if ($isEloquent === true) {
                return $instance->getAttribute($field->getGetter());
            }

            return $instance->{$field->getGetter()}();
        }

        return null;
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function setCustomFieldFilter($closure)
    {
        $this->customFieldFilter = $closure;
    }

}
