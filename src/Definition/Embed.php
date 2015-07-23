<?php
namespace Rested\Definition;

class Embed
{

    const SECURITY_ATTRIBUTE = 'rested_embed';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var array
     */
    protected $userData;

    public function __construct($name, $routeName, $userData)
    {
        $this->name = $name;
        $this->routeName = $routeName;
        $this->userData = $userData;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getUserData()
    {
        return $this->userData;
    }
}
