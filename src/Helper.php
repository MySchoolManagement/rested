<?php
namespace Rested;

use Symfony\Component\Security\Core\Role\Role;

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
}
