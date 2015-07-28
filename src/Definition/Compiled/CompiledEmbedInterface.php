<?php
namespace Rested\Definition\Compiled;

interface CompiledEmbedInterface
{

    /**
     * Gets the roles for the field.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleInterface
     */
    public function getRoles();
}
