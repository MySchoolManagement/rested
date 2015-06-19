<?php
namespace Rested\Definition;

use Rested\Context;
use Rested\Definition\Mapping;
use Rested\Helper;
use Rested\Traits\ExportTrait;

class ActionDefinition
{

    const TYPE_COLLECTION = 'collection';
    const TYPE_INSTANCE = 'instance';

    private $definition;

    private $callable;

    private $description;

    private $requiredPermission;

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

    public function getName()
    {
        return $this->name;
    }

    public function getRequiredPermission()
    {
        if ($this->requiredPermission !== null) {
            return $this->requiredPermission;
        }

        // @TODO: $this->requiredPermission = sprintf('ROLE_API_%s_%s_%s', Util::formatPermissionString($this->definition->getInitialContext()->getTag()), Util::formatPermissionString($this->definition->getName()), Util::formatPermissionString($this->getName()));

        return $this->requiredPermission;
    }

    public function getRouteName()
    {
        $slug = Helper::makeSlug($this->getDefinition()->getEndpoint(), ['delimiter' => '_']);
        $type = $this->getTypeName();

        return sprintf('rested_%s_%s', $slug, $type);
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
            case self::TYPE_INSTANCE:
                return 'instance';

            case self::TYPE_COLLECTION:
                return 'collection';
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
                return 'get';
        }

        return 'get';
    }

    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    /**
     * Sets the input fields to match the given mapping.
     *
     * @param Mapping $mapping
     */
    public function setModelMapping(Mapping $mapping)
    {
        foreach ($mapping->getFields() as $field) {
            if ($field->isModel() == true) {
                $this->addInput($field->getName(), $field->getType(), null, $field->isRequired(), $field->getDescription());
            }
        }
        
        return $this;
    }

    public function setSummary($value)
    {
        $this->summary = $value;

        return $this;
    }
}
