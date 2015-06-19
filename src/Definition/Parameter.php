<?php
namespace Rested\Definition;

class Parameter
{

    const TYPE_ARRAY = 'array';
    const TYPE_DATE = 'date';
    const TYPE_INT = 'int';
    const TYPE_SLASH = 'slash';
    const TYPE_STRING = 'string';
    const TYPE_UUID = 'uuid';

    private $types;

    private $typeFriendly;

    private $description;

    private $defaultValue;

    private $name;

    private $required;

    public function __construct($name, $type, $defaultValue, $description, $required = false)
    {
        $this->name = $name;
        $this->typeFriendly = $type;
        $this->types = explode('|', $type);
        $this->defaultValue = $defaultValue;
        $this->description = $description;
        $this->required = $required;
    }

    public function acceptAnyValue()
    {
        return in_array('mixed', $this->types);
    }

    public function expects($type)
    {
        return in_array($type, $this->types);
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->typeFriendly;
    }

    public function getTypeFriendly()
    {
        return $this->typeFriendly;
    }

    public function getValidatorPattern($full = true)
    {
        $patterns = array();

        foreach ($this->types as $type) {
            switch ($type) {
                case self::TYPE_DATE:
                    $patterns[] = '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])';
                    break;

                case self::TYPE_INT:
                    $patterns[] = '\d+';
                    break;

                case self::TYPE_STRING:
                    $patterns[] = '\w+';
                    break;

                case self::TYPE_ARRAY:
                    break;

                case self::TYPE_UUID:
                    $patterns[] = '.*\-.*\-.*\-.*\-.*';
                    break;

                case TYPE_SLASH:
                    $patterns[] = '.+';
                    break;

                default:
                    throw new \Exception(sprintf('Unsupported resource parameter type \'%s\' for \'%s\'', $type, $this->getName()));
                    break;
            }
        }

        if ($full == true) {
            return sprintf('/%s/', join('|', $patterns));
        } else {
            return join('|', $patterns);
        }
    }

    public function isRequired()
    {
        return $this->required;
    }
}
