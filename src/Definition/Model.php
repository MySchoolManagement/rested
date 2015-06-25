<?php
namespace Rested\Definition;

use Nocarrier\Hal;

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
        $user = $this->resourceDefinition->getResource()->getUser();

        if ($obj === null) {
            $class = $this->getDefiningClass();
            $obj = new $class();
        }

        if (method_exists($obj, 'setLocale') === true) {
            $obj->setLocale($locale);
        }

        // TODO: should we throw an exception when data is supplied for a field that cannot be set?
        foreach ($data as $key => $value) {
            if (($field = $this->findField($key)) !== null) {
                // do they have permission to set this field?
                if ($user->isGranted($def->getRoleNames('set')) == false) {
                    continue;
                }

                if ($field->isModel() === true) {
                    $setter = $field->getSetter();
                    $obj->$setter($value);
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

        if ($expand == true) {
            $fields = $this->getFields();

            foreach ($fields as $def) {
                // a null context means all fields except expansions
                if (($context === null) || ($forceAllFields == true) || ($context->wantsField($def->getName()) == true)) {
                    // do they have permission to get this field?
                    if ($user->isGranted($def->getRoleNames('get')) == false) {
                        continue;
                    }

                    $callable = $def->getGetter();

                    if ($callable !== null) {
                        if (is_array($callable) == true) {
                            $val = call_user_func($callable, $instance);
                        } else {
                            $val = $instance->$callable();
                        }

                        $e[$def->getName()] = $this->exportValue($val);
                    }
                }
            }
        }

        return new Hal($href, $e);
    }

    public function exportAll($expand = true)
    {
        return $this->export($expand, true);
    }

    private function exportValue($value)
    {
        $return = null;

        if ((is_array($value) === true) || ($value instanceof \ArrayObject)) {
            $return = [];

            foreach ($value as $key => $otherValue) {
                $return[$key] = $this->exportValue($otherValue);
            }
        } else if ($value instanceof \DateTime) {
            $return = $value->format(\DateTime::ISO8601);
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
