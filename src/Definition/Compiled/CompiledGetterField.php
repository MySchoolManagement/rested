<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\SetterField;

class CompiledGetterField extends SetterField implements CompiledFieldInterface
{

    /**
     * @var \Symfony\Component\Security\Core\Role\RoleInterface
     */
    private $roles = [];

    public function __construct(array $roles, $name, $callback, $description, $dataType, $rel = null)
    {
        parent::__construct($name, $callback, $description, $dataType, $rel);

        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
