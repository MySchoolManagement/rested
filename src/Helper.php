<?php
namespace Rested;

use Rested\Definition\Parameter;

class Helper
{

    /**
     * @return mixed
     */
    public static function processValue($value, $dataType)
    {
        if (is_array($value) === true) {
            foreach ($value as $k => $v) {
                $value[$k] = static::processValue($v, $dataType);
            }
        } else {
            switch ($dataType) {
                case Parameter::TYPE_BOOL:
                    if (($value === 'true') || ($value === '1')) {
                        return true;
                    } else {
                        return false;
                    }
                    break;

                case Parameter::TYPE_DATE:
                    return \DateTime::createFromFormat('Y-m-d', $value);

                case Parameter::TYPE_DATETIME:
                    return \DateTime::createFromFormat(\Datetime::ISO8601, $value);

                case Parameter::TYPE_INT:
                    return (int)$value;
            }
        }

        return $value;
    }

    public static function convertArrayValuesTo(array &$arr, $type)
    {
        foreach ($arr as $k => $v) {
            settype($arr[$k], $type);
        }
    }

    public static function makeValidationMessages($validator)
    {
        $failed = $validator->failed();
        $validationMessages = $validator->messages();
        $messages = [];

        foreach ($failed as $field => $rules) {
            $messages[$field] = [];

            foreach ($rules as $rule => $parameters) {
                $messages[$field][$rule] = $validationMessages->first($field);
            }
        }

        return $messages;
    }
}
