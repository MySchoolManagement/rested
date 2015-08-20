<?php
namespace Rested;

class Helper
{

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

    public static function toUnderscore($name)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
    }
}
