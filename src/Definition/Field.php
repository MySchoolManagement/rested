<?php
namespace Rested\Definition;

class Field
{

    private $getter;
    private $setter;
    private $description;
    private $model;
    private $name;
    private $requiredPermission;
    private $type;
    private $validationParameters;

    public function __construct(Model $model, $name, $getter, $setter, $description, $type, $validationParameters = null)
    {
        $this->getter = $getter;
        $this->setter = $setter;
        $this->description = $description;
        $this->model = $model;
        $this->name = $name;
        $this->type = $type;
        $this->validationParameters = $validationParameters;
    }

    /**
     * Gets the callback responsible for retrieving the value of this field.
     *
     * @return callable
     */
    public function getGetter()
    {
        return $this->getter;
    }

    /**
     * Gets the callback responsible for setting the value of this field.
     *
     * @return callable
     */
    public function getSetter()
    {
        return $this->setter;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \Rested\Definition\InstanceDefinition
     */
    public function getInstanceDefinition()
    {
        return $this->model->getInstanceDefinition();
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

        // @todo: $this->requiredPermission = sprintf('ROLE_DATA_%s_%s', Util::formatPermissionString($this->getMapping()->getDefiningClass()), Util::formatPermissionString($this->getName()));

        return $this->requiredPermission;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValidationParameters()
    {
        return $this->validationParameters;
    }

    /**
     * Is this field part of the underlying model?
     *
     * @return boolean
     */
    public function isModel()
    {
        return ($this->getSetter() !== null);
    }
}
