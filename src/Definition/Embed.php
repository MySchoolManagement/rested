<?php
namespace Rested\Definition;

class Embed
{

    private $name;

    private $routeName;

    private $userData;

    public function __construct(Model $model, $name, $routeName, $userData)
    {
        $this->name = $name;
        $this->routeName = $routeName;
        $this->userData = $userData;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getUserData()
    {
        return $this->userData;
    }
}
