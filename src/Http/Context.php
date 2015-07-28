<?php
namespace Rested\Http;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;

class Context implements ContextInterface
{

    /**
     * @var string
     */
    protected $actionType;

    /**
     * @var \Rested\Definition\Compiled\CompiledResourceDefinitionInterface
     */
    protected $resourceDefinition;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var array
     */
    protected $parameters = [
        'embed' => [],
        'fields' => [],
        'filters' => [],
        'limit' => RequestParser::DEFAULT_LIMIT,
        'offset' => RequestParser::DEFAULT_OFFSET,
    ];

    public function __construct(
        array $parameters,
        $actionType,
        $routeName,
        CompiledResourceDefinitionInterface $resourceDefinition)
    {
        $this->actionType = $actionType;
        $this->parameters = $parameters;
        $this->resourceDefinition = $resourceDefinition;
        $this->routeName = $routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->getResourceDefinition()->findActionByRouteName($this->routeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmbeds()
    {
        return $this->parameters['embed'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->parameters['fields'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->parameters['filters'];
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getResourceDefinition()
    {
        return $this->resourceDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return $this->parameters['limit'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset()
    {
        return $this->parameters['offset'];
    }

    /**
     * {@inheritdoc}
     */
    public function wantsEmbed($name)
    {
        if ((array_key_exists('all', $this->parameters['embed']) === true)
            || (array_key_exists($name, $this->parameters['embed']) === true)
            || (in_array($name, $this->parameters['embed']) === true)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function wantsField($name)
    {
        if ((in_array('all', $this->parameters['fields']) === true)
            || (in_array($name, $this->parameters['fields']) === true)) {
            return true;
        }

        return false;
    }
}
