<?php
namespace Rested\Definition;

use Rested\Definition\Parameter;
use Rested\Helper;

class Field
{

    private $description;
    private $getter;
    private $model;
    private $name;
    private $setter;
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
     * @return \Rested\Definition\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTypeValidatorName()
    {
        return Parameter::getValidator($this->getType());
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
