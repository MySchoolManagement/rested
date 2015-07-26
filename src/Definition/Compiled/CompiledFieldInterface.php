<?php
namespace Rested\Definition\Compiled;

interface CompiledFieldInterface
{

    /**
     * Gets the roles for the field.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleInterface
     */
    public function getRoles();
}
