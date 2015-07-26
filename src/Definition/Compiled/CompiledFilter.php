<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\Filter;

class CompiledFilter extends Filter implements CompiledFilterInterface
{

    /**
     * @var \Symfony\Component\Security\Core\Role\RoleInterface
     */
    private $roles = [];

    public function __construct(array $roles, $name, $callback, $description, $dataType)
    {
        parent::__construct($name, $callback, $description, $dataType);

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
