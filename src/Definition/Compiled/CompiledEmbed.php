<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\Embed;

class CompiledEmbed extends Embed implements CompiledEmbedInterface
{

    /**
     * @var \Symfony\Component\Security\Core\Role\RoleInterface
     */
    private $roles = [];

    public function __construct(array $roles, $name, $routeName, $userData)
    {
        parent::__construct($name, $routeName, $userData);

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
