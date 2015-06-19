<?php
namespace Rested\Definition;

class Filter
{

    private $callable;

    private $description;

    private $mapping;

    private $model;

    private $name;

    private $required;

    private $requiredPermission;

    private $type;

    public function __construct(Mapping $mapping, $name, $callable, $description, $type)
    {
        $this->callable = $callable;
        $this->description = $description;
        $this->mapping = $mapping;
        $this->name = $name;
        $this->type = $type;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getMapping()
    {
        return $this->mapping;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getRequiredPermission()
    {
        if ($this->requiredPermission !== null) {
            return $this->requiredPermission;
        }

        /*$this->requiredPermission = sprintf('ROLE_FILTER_%s_%s',
			Util::formatPermissionString($this->getMapping()->getDefiningClass()),
			Util::formatPermissionString($this->getName()
		));*/

        return $this->requiredPermission;
    }

    public function getType()
    {
        return $this->type;
    }
}
