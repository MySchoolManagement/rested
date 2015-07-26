<?php
namespace Rested;

use Rested\Definition\ActionDefinitionInterface;
use Rested\Definition\Field;
use Rested\Definition\Filter;

interface NameGeneratorInterface
{

    /**
     * @return \Symfony\Component\Security\Core\Role\RoleInterface[]
     */
    public function rolesForAction(ActionDefinitionInterface $action, $pathPrefix);

    /**
     * @return \Symfony\Component\Security\Core\Role\RoleInterface[]
     */
    public function rolesForField(Field $field, $securityAttribute, $pathPrefix);

    /**
     * @return \Symfony\Component\Security\Core\Role\RoleInterface[]
     */
    public function rolesForFilter(Filter $filter, $pathPrefix);

    /**
     * @return string
     */
    public function roleName();

    /**
     * Generates a route name for an action.
     *
     * @param \Rested\Definition\ActionDefinitionInterface $action Action to generate a route name for.
     * @param string $prefix Prefix for the route, typically the name of the resource.
     * @return string
     */
    public function routeName(ActionDefinitionInterface $action, $prefix);

    /**
     * Generates a friendly slug from a list of strings.
     *
     * @param array $array Components to generate a slug from.
     * @param array $options Options to customize the slug. See makeSlug.
     * @return string
     */
    public function slugFromArray(array $array = [], array $options = []);

    /**
     * Create a web friendly URL slug from a string.
     *
     * Although supported, transliteration is discouraged because
     *     1) most web browsers support UTF-8 characters in URLs
     *     2) transliteration causes a loss of information
     *
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     *
     * @param string $str
     * @param array $options
     * @return string
     */
    public function slug($str, $options = []);
}
