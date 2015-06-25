<?php
namespace Rested\Definition;

use Rested\Context;
use Rested\Definition\Mapping;
use Rested\Helper;
use Rested\Traits\ExportTrait;

class ActionDefinition
{

    const TYPE_COLLECTION = 'collection';
    const TYPE_CREATE = 'create';
    const TYPE_INSTANCE = 'instance';

    private $definition;

    private $cacheRoleNames;

    private $cacheRouteName;

    private $callable;

    private $description;

    private $modelOverride;

    private $summary;

    private $type;

    private $filters = [];

    private $inputs = [];

    private $tokens = [];

    public function getMapping()
    {
        return Mapping::create('Action Definition', self::class)
                ->addField('description',         'string', 'getDescription',        null, false, 'Description of the endpoint.')
                ->addField('required_permission', 'string', 'getRequiredPermission', null, false, 'Permission required to call this action.')
                ->addField('summary',             'string', 'getSummary',            null, false, 'Summary of the endpoint\'s purpose.')
                ->addField('verb',                'string', 'getVerb',               null, false, 'HTTP verb to use when calling this action.')
            ;
    }

    public function __construct(ResourceDefinition $definition, $type, $callable)
    {
        $this->callable = $callable;
        $this->definition = $definition;
        $this->type = $type;
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

    public function findFilter($name)
    {
        foreach ($this->filters as $filter) {
            if (strcasecmp($name, $filter->getName()) == 0) {
                return $filter;
            }
        }

        return null;
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

    /**
     * @return \Rested\Definition\Model
     */
    public function getModel()
    {
        if ($this->modelOverride !== null) {
            return $this->modelOverride;
        }

        return $this->getDefinition()->getInstanceDefinition();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRoleNames()
    {
        if ($this->cacheRoleNames !== null) {
            return $this->cacheRoleNames;
        }

        $endpoint = $this->getDefinition()->getEndpoint();
        $type = $this->getTypeName();

        $loose = Helper::makeRoleName($endpoint);
        $specific = Helper::makeRoleName($endpoint, $type);

        return ($this->cacheRoleNames = [$loose, $specific]);
    }

    public function getRouteName()
    {
        if ($this->cacheRouteName !== null) {
            return $this->cacheRouteName;
        }

        $endpoint = $this->getDefinition()->getEndpoint();
        $type = $this->getTypeName();

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

    public function getTypeName()
    {
        switch ($this->getType()) {
            case self::TYPE_COLLECTION:
                return 'collection';

            case self::TYPE_CREATE:
                return 'create';

            case self::TYPE_INSTANCE:
                return 'instance';
        }

        return 'unknown';
    }

    public function getUrl()
    {
        $url = $this->definition->getUrl();
        $tokens = $this->getTokens();
        $tokenNames = [];

        foreach ($tokens as $token) {
            $tokenNames[] = sprintf('{%s}', $token->getName());
        }

        return Helper::makeUrl($url, $tokenNames);
    }

    public function getVerb()
    {
        switch ($this->type) {
            case ActionDefinition::TYPE_COLLECTION:
            case ActionDefinition::TYPE_INSTANCE:
                return 'get';

            case ActionDefinition::TYPE_CREATE:
                return 'post';
        }

        return 'get';
    }

    public function setDescription($value)
    {
        $this->description = $value;

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
}
