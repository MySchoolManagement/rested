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

    public static function createRolesForObject($attribute, $object)
    {
        $class = is_string($object) ? $object : get_class($object);
        $roles = [];

        if ($class === 'Rested\Definition\ActionDefinition') {
            $endpoint = $object->getDefinition()->getEndpoint();
            $name = $object->getName();
            $loose = Helper::makeRoleName($endpoint);
            $specific = Helper::makeRoleName($endpoint, $name);

            $roles =  [$loose, $specific];
        } else if ($class === 'Rested\Definition\Field') {
            $endpoint = $object->getModel()->getDefinition()->getEndpoint();

            $roles = [
                Helper::makeRoleName($endpoint, 'field', $object->getName()),
                Helper::makeRoleName($endpoint, 'field', $object->getName(), $attribute),
                Helper::makeRoleName($endpoint, 'field', 'all'),
                Helper::makeRoleName($endpoint, 'field', 'all', $attribute),
            ];
        } else if ($class === 'Rested\Definition\Filter') {
            $endpoint = $object->getModel()->getDefinition()->getEndpoint();

            $roles = [
                Helper::makeRoleName($endpoint, 'filter', $object->getName()),
                Helper::makeRoleName($endpoint, 'filter', 'all'),
            ];
        } else {
            throw new \InvalidArgumentException(get_class($object) . ' is not supported');
        }

        foreach ($roles as $idx => $role) {
            $roles[$idx] = new Role($role);
        }

        return $roles;
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

    public static function makeRoleName()
    {
        $parts = func_get_args();
        array_unshift($parts, 'ROLE_RESTED');

        return mb_strtoupper(self::makeSlugFromArray($parts, ['delimiter' => '_']));
    }

}
