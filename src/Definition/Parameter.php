<?php
namespace Rested\Definition;

use Ramsey\Uuid\Uuid;

class Parameter
{

    const TYPE_ARRAY = 'array';
    const TYPE_BOOL = 'bool';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_INT = 'int';
    const TYPE_SLASH = 'slash';
    const TYPE_STRING = 'string';
    const TYPE_UUID = 'uuid';

    private $type;

    private $description;

    private $defaultValue;

    private $name;

    private $required;

    public function __construct($name, $type, $defaultValue, $description, $required = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
        $this->description = $description;
        $this->required = $required;
    }

    public function acceptAnyValue()
    {
        return $this->expects('mixed');
    }

    public function expects($type)
    {
        return ($this->type === $type);
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
        return $this->type;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public static function getValidator($type)
    {
        switch ($type) {
            case self::TYPE_BOOL:
                return 'boolean';

            case self::TYPE_DATE:
                return 'date_format:Y-m-d';

            case self::TYPE_DATETIME:
                return sprintf('date_format:%s'. \DateTime::ISO8601);

            case self::TYPE_INT:
                return 'numeric';

            case self::TYPE_STRING:
                return 'string';

            case self::TYPE_ARRAY:
                return 'array';

            case self::TYPE_UUID:
                return 'uuid';
        }

        return null;
    }

    public static function getValidatorPattern($type)
    {
        switch ($type) {
            case self::TYPE_BOOL:
                return '[01]|true|false';

            case self::TYPE_DATE:
                return '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])';

            case self::TYPE_DATETIME:
                return sprintf('date_format:%s'. \DateTime::ISO8601);

            case self::TYPE_INT:
                return '\d+';

            case self::TYPE_STRING:
                return '\w+';

            case self::TYPE_ARRAY:
                return '';

            case self::TYPE_UUID:
                return Uuid::VALID_PATTERN;
        }

        return null;
    }
}
