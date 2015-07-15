<?php
namespace Rested\Definition;

use Rested\Context;
use Rested\Definition\Mapping;
use Rested\Helper;
use Rested\Security\AccessVoter;
use Rested\Traits\ExportTrait;
use Symfony\Component\HttpFoundation\Request;

class ActionDefinition
{

    const TYPE_COLLECTION = 'collection';
    const TYPE_CREATE = 'create';
    const TYPE_DELETE = 'delete';
    const TYPE_INSTANCE = 'instance';
    const TYPE_INSTANCE_ACTION = 'instance_action';
    const TYPE_UPDATE = 'update';

    private $appendUrl;

    private $definition;

    private $cacheRouteName;

    private $callable;

    private $contentType = 'application/json';

    private $description;

    private $method;

    private $modelOverride;

    private $name;

    private $summary;

    private $type;

    private $filters = [];

    private $inputs = [];

    private $tokens = [];

    public function __construct(ResourceDefinition $definition, $type, $name)
    {
        // convert hyphenated to camelcase
        $this->callable = preg_replace_callback('!\-[a-zA-Z]!', function($matches) {
            return strtoupper(str_replace('-', '', $matches[0]));
        }, $name);

        $this->definition = $definition;
        $this->name = $name;
        $this->type = $type;
        $this->method = self::methodFromType($type);
    }

    public function addFilter($name, $type, $callable, $description)
    {
        $this->filters[] = new Filter($this, $name, $callable, $description, $type);

        return $this;
    }

    public function addInput($name, $type, $defaultValue, $required, $description)
    {
        $this->inputs[] = new Parameter($name, $type, $defaultValue, $description, $required);

        return $this;
    }

    public function addToken($name, $type, $defaultValue = null, $description = null)
    {
        $this->tokens[] = new Parameter($name, $type, $defaultValue, $description);

        return $this;
    }

    public function appendUrl($part)
    {
        $this->appendUrl = $part;
    }

    public function findFilter($name)
    {
        foreach ($this->filters as $filter) {
            if (strcasecmp($name, $filter->getName()) == 0) {
                return $filter;
            }
        }

        return null;
    }

    public function getAppendUrl()
    {
        return $this->appendUrl;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getInputs()
    {
        return $this->inputs;
    }

    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return \Rested\Definition\Model
     */
    public function getModel()
    {
        if ($this->modelOverride !== null) {
            return $this->modelOverride;
        }

        return $this->getDefinition()->getModel();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getRouteName()
    {
        if ($this->cacheRouteName !== null) {
            return $this->cacheRouteName;
        }

        $endpoint = $this->getDefinition()->getEndpoint();
        $type = $this->getName();

        return ($this->cacheRouteName = Helper::makeRouteName($endpoint, $type));
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUrl()
    {
        $tokens = $this->getTokens();
        $parts = [];

        foreach ($tokens as $token) {
            $parts[] = sprintf('{%s}', $token->getName());
        }

        if (mb_strlen($this->appendUrl) > 0) {
            $parts[] = $this->appendUrl;
        }

        return $this->getDefinition()->getUrl($parts);
    }

    public function setCallable($method)
    {
        $this->callable = $method;

        return $this;
    }

    public function setContentType($type)
    {
        $this->contentType = $type;

        return $this;
    }

    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param \Rested\Definition\Model $model
     * @return $this
     */
    public function setModelOverride(Model $model = null)
    {
        $this->modelOverride = $model;

        return $this;
    }

    public function setSummary($value)
    {
        $this->summary = $value;

        return $this;
    }

    private static function methodFromType($type)
    {
        switch ($type) {
            case ActionDefinition::TYPE_CREATE:
                return Request::METHOD_POST;

            case ActionDefinition::TYPE_DELETE:
                return Request::METHOD_DELETE;

            case ActionDefinition::TYPE_UPDATE:
                return Request::METHOD_PUT;
        }

        return Request::METHOD_GET;
    }
}
