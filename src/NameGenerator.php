<?php
namespace Rested;

use Rested\Definition\ActionDefinitionInterface;
use Rested\Definition\Field;
use Rested\Definition\Filter;
use Symfony\Component\Security\Core\Role\Role;

class NameGenerator implements NameGeneratorInterface
{

    const ROUTE_PREFIX = 'rested_';

    /**
     * {@inheritdoc}
     */
    public function rolesForAction(ActionDefinitionInterface $action, $pathPrefix)
    {
        $name = $action->getId();
        $loose = $this->roleName($pathPrefix);
        $specific = $this->roleName($pathPrefix, $name);

        return $this->makeRoles([$loose, $specific]);
    }

    /**
     * {@inheritdoc}
     */
    public function rolesForField(Field $field, $securityAttribute, $pathPrefix)
    {
        $roles = [
            $this->roleName($pathPrefix, 'field', $field->getName()),
            $this->roleName($pathPrefix, 'field', $field->getName(), $securityAttribute),
            $this->roleName($pathPrefix, 'field', 'all'),
            $this->roleName($pathPrefix, 'field', 'all', $securityAttribute),
        ];

        return $this->makeRoles($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function rolesForFilter(Filter $filter, $pathPrefix)
    {
        $roles = [
            $this->roleName($pathPrefix, 'filter', $filter->getName()),
            $this->roleName($pathPrefix, 'filter', 'all'),
        ];

        return $this->makeRoles($roles);
    }

    protected function makeRoles(array $roles)
    {
        foreach ($roles as $idx => $role) {
            $roles[$idx] = new Role($role);
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function roleName()
    {
        $options = [
            'delimiter' => '_',
        ];
        $components = func_get_args();

        array_unshift($components, 'ROLE_RESTED');

        return mb_strtoupper($this->slugFromArray($components, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function routeName(ActionDefinitionInterface $action, $prefix)
    {
        $components = [
            $prefix,
            $action->getId(),
        ];
        $options = [
            'delimiter' => '_',
        ];

        return self::ROUTE_PREFIX . $this->slugFromArray($components, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function slugFromArray(array $array = [], array $options = [])
    {
        $delimiter = array_key_exists('delimiter', $options) ? $options['delimiter'] : '-';
        $str = join($delimiter, $array);

        return $this->slug($str, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function slug($str, $options = [])
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => [],
            'transliterate' => false,
        );

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = [
            // Latin
            '?' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', '?' => 'A', '?' => 'AE', '?' => 'C',
            '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'E', '?' => 'I', '?' => 'I', '?' => 'I', '?' => 'I',
            '?' => 'D', '?' => 'N', '?' => 'O', '?' => 'O', '?' => 'O', '?' => 'O', '?' => 'O', '?' => 'O',
            '?' => 'O', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'U', '?' => 'Y', '?' => 'TH',
            '?' => 'ss',
            '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'a', '?' => 'ae', '?' => 'c',
            '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'e', '?' => 'i', '?' => 'i', '?' => 'i', '?' => 'i',
            '?' => 'd', '?' => 'n', '?' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', '?' => 'o', '?' => 'o',
            '?' => 'o', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'u', '?' => 'y', '?' => 'th',
            '?' => 'y',

            // Latin symbols
            '?' => '(c)',

            // Greek
            '?' => 'A', '?' => 'B', '?' => 'G', '?' => 'D', '?' => 'E', '?' => 'Z', '?' => 'H', '?' => '8',
            '?' => 'I', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => '3', '?' => 'O', '?' => 'P',
            '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'Y', '?' => 'F', '?' => 'X', '?' => 'PS', '?' => 'W',
            '?' => 'A', '?' => 'E', '?' => 'I', '?' => 'O', '?' => 'Y', '?' => 'H', '?' => 'W', '?' => 'I',
            '?' => 'Y',
            '?' => 'a', '?' => 'b', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'z', '?' => 'h', '?' => '8',
            '?' => 'i', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n', '?' => '3', '?' => 'o', '?' => 'p',
            '?' => 'r', '?' => 's', '?' => 't', '?' => 'y', '?' => 'f', '?' => 'x', '?' => 'ps', '?' => 'w',
            '?' => 'a', '?' => 'e', '?' => 'i', '?' => 'o', '?' => 'y', '?' => 'h', '?' => 'w', '?' => 's',
            '?' => 'i', '?' => 'y', '?' => 'y', '?' => 'i',

            // Turkish
            '?' => 'S', '?' => 'I', '?' => 'C', '?' => 'U', '?' => 'O', '?' => 'G',
            '?' => 's', '?' => 'i', '?' => 'c', '?' => 'u', '?' => 'o', '?' => 'g',

            // Russian
            '?' => 'A', '?' => 'B', '?' => 'V', '?' => 'G', '?' => 'D', '?' => 'E', '?' => 'Yo', '?' => 'Zh',
            '?' => 'Z', '?' => 'I', '?' => 'J', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => 'O',
            '?' => 'P', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U', '?' => 'F', '?' => 'H', '?' => 'C',
            '?' => 'Ch', '?' => 'Sh', '?' => 'Sh', '?' => '', '?' => 'Y', '?' => '', '?' => 'E', '?' => 'Yu',
            '?' => 'Ya',
            '?' => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'yo', '?' => 'zh',
            '?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n', '?' => 'o',
            '?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c',
            '?' => 'ch', '?' => 'sh', '?' => 'sh', '?' => '', '?' => 'y', '?' => '', '?' => 'e', '?' => 'yu',
            '?' => 'ya',

            // Ukrainian
            '?' => 'Ye', '?' => 'I', '?' => 'Yi', '?' => 'G',
            '?' => 'ye', '?' => 'i', '?' => 'yi', '?' => 'g',

            // Czech
            '?' => 'C', '?' => 'D', '?' => 'E', '?' => 'N', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U',
            '?' => 'Z',
            '?' => 'c', '?' => 'd', '?' => 'e', '?' => 'n', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u',
            '?' => 'z',

            // Polish
            '?' => 'A', '?' => 'C', '?' => 'e', '?' => 'L', '?' => 'N', '?' => 'o', '?' => 'S', '?' => 'Z',
            '?' => 'Z',
            '?' => 'a', '?' => 'c', '?' => 'e', '?' => 'l', '?' => 'n', '?' => 'o', '?' => 's', '?' => 'z',
            '?' => 'z',

            // Latvian
            '?' => 'A', '?' => 'C', '?' => 'E', '?' => 'G', '?' => 'i', '?' => 'k', '?' => 'L', '?' => 'N',
            '?' => 'S', '?' => 'u', '?' => 'Z',
            '?' => 'a', '?' => 'c', '?' => 'e', '?' => 'g', '?' => 'i', '?' => 'k', '?' => 'l', '?' => 'n',
            '?' => 's', '?' => 'u', '?' => 'z'
        ];

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }
}
