<?php
namespace Rested\Definition\Compiled;

interface CompiledFilterInterface
{

    /**
     * Gets the roles for the filter.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleInterface
     */
    public function getRoles();
}
