<?php
namespace Rested\Definition;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Nocarrier\Hal;
use Rested\Response;

/**
 * Maps an external representation of a resource to an actual object.
 *
 * This is done through the use of getter and setter methods which exist on
 * the object.
 *
 * This allows for the creation of "virtual" resources that don't actually map
 * to any single object in the system.
 *
 * The fields added to a mapping are used to build the representation that is
 * sent to the client.
 *
 * For example:
 *
 * {
 * "name": "xyz"
 * "description": "a super description"
 * }
 */
class Model
{

    private $class;

    private $fields = [];

    private $primaryKeyField = 'uuid';

    private $resourceDefinition;

    /**
     * Constructor
     *
     * @param string $class
     */
    public function __construct(ResourceDefinition $resourceDefinition, $class)
    {
        $this->class = $class;
        $this->resourceDefinition = $resourceDefinition;
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
    public function add($name, $type, $getter, $setter, $description, $validationParameters = null)
    {
        $this->fields[] = new Field($this, $name, $getter, $setter, $description, $type, $validationParameters);

        return $this;
    }

    /**
     * Using the mapping information, applies the field data in $data
     * to $obj.
     *
     * @param string $locale
     *            Locale of the data to set.
     * @param array $data
     *            Data to apply.
     * @param object $obj
     *            Object to apply the mapping to. If this is null, a new
     *            instance is created.
     */
    public function apply($locale, array $data, $obj = null)
    {
        $definition = $this->resourceDefinition;
        $user = $definition->getResource()->getUser();

        if ($obj === null) {
            $class = $this->getDefiningClass();
            $obj = new $class();
        }

        if (method_exists($obj, 'setLocale') === true) {
            $obj->setLocale($locale);
        }

        $isEloquent = $obj instanceof EloquentModel;

        // TODO: should we throw an exception when data is supplied for a field that cannot be set?
        foreach ($data as $key => $value) {
            if (($field = $this->findField($key)) !== null) {
                // do they have permission to set this field?
                if ($user->isGranted($field->getRoleNames('set')) == false) {
                    continue;
                }

                if ($field->isModel() === true) {
                    $setter = $field->getSetter();

                    if ($isEloquent === true) {
                        $obj->setAttribute($setter, $value);
                    } else {
                        $obj->{$setter}($value);
                    }
                }
            }
        }

        return $obj;
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
     * @return \Rested\Definition\ResourceDefinition
     */
    public function getDefinition()
    {
        return $this->resourceDefinition;
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
    public function setField($name, $type, $getter, $setter, $description, $validationParameters = null)
    {
        $this->add($name, $type, $getter, $setter, $description, $validationParameters);

        return $this;
    }

    public function export($instance, $expand = true, $forceAllFields = false)
    {
        $e = [];
        $href = '';
        $resource = $this->resourceDefinition->getResource();
        $context = $resource->getContext();
        $user = $resource->getUser();

        // add href if we have a context
        if ($context !== null) {
            $href = $context->getResource()->createInstanceHref($instance);
        }

        if ($expand === true) {
            $fields = $this->getFields();
            $isEloquent = $instance instanceof EloquentModel;

            foreach ($fields as $def) {
                // a null context means all fields except expansions
                if (($context === null) || ($forceAllFields === true) || ($context->wantsField($def->getName()) === true)) {
                    // do they have permission to get this field?
                    if ($user->isGranted($def->getRoleNames('get')) == false) {
                        continue;
                    }

                    $callable = $def->getGetter();

                    if ($callable !== null) {
                        if (is_array($callable) === true) {
                            $val = call_user_func($callable, $instance);
                        } else {
                            if ($isEloquent === true) {
                                $val = $instance->getAttribute($callable);
                            } else {
                                $val = $instance->$callable();
                            }
                        }

                        $e[$def->getName()] = $this->exportValue($def, $val);
                    }
                }
            }
        }

        return Response::createInstance($resource, $href, $e);
    }

    public function exportAll($instance, $expand = true)
    {
        return $this->export($instance, $expand, true);
    }

    private function exportValue(Field $field, $value)
    {
        $return = null;

        if ((is_array($value) === true) || ($value instanceof \ArrayObject)) {
            $return = [];

            foreach ($value as $key => $otherValue) {
                $return[$key] = $this->exportValue($field, $otherValue);
            }
        } else if ($value instanceof \DateTime) {
            if ($field->getType() === Parameter::TYPE_DATE) {
                $return = $value->format('Y-m-d');
            } else {
                $return = $value->format(\DateTime::ISO8601);
            }
        } else if (is_object($value) === true) {
            throw new \Exception('NOT IMPLEMENTED');
            //$return = $value->export($context);
        } else {
            $return = $value;
        }

        return $return;
    }

    public function getDefiningClass()
    {
        return $this->class;
    }

    public static function create(ResourceDefinition $resourceDefinition, $class)
    {
        return new Model($resourceDefinition, $class);
    }
}
