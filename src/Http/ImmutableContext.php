<?php
namespace Rested\Http;

use Rested\Definition\ImmutableResourceDefinition;
use Rested\Definition\ResourceDefinition;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ImmutableContext
{

    /**
     * @var string
     */
    private $actionType;

    /**
     * @var \Rested\Definition\ImmutableResourceDefinition
     */
    private $definition;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $parameters = [
        'embed' => [],
        'fields' => [],
        'filters' => [],
        'limit' => 0,
        'offset' => 0,
    ];

    public function __construct(
        array $parameters,
        $actionType,
        $routeName,
        ResourceDefinition $resourceDefinition,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->actionType = $actionType;
        $this->definition = new ImmutableResourceDefinition($authorizationChecker, $resourceDefinition);
        $this->parameters = $parameters;
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * @return \Rested\Definition\ImmutableResourceDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return string[]
     */
    public function getFields()
    {
        return $this->parameters['fields'];
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function getFilterValue($name)
    {
        if ((array_key_exists($name, $this->parameters['filters']) === true)
            && ($this->parameters['filters'][$name] !== null)) {
            return $this->parameters['filters'][$name];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getLimit()
    {
        return $this->parameters['limit'];
    }

    public function getOffset()
    {
        return $this->parameters['offset'];
    }

    public function wantsEmbeddable($name)
    {
        if ((array_key_exists('all', $this->parameters['embed']) === true)
            || (array_key_exists($name, $this->parameters['embed']) === true)) {
            return true;
        }

        return false;
    }

    public function wantsField($name)
    {
        if ((in_array('all', $this->parameters['fields']) === true)
            || (in_array($name, $this->parameters['fields']) === true)) {
            return true;
        }

        return false;
    }
}
