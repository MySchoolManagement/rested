<?php
namespace Rested\Definition;

class Field
{

    private $dataType;
    private $description;
    private $getter;
    private $name;
    private $setter;
    private $validationParameters;
    private $rel;

    public function __construct($name, $getter, $setter, $description, $dataType, $validationParameters = null, $rel = null)
    {
        $this->getter = $getter;
        $this->setter = $setter;
        $this->dataType = $dataType;
        $this->description = $description;
        $this->name = $name;
        $this->validationParameters = $validationParameters;
        $this->rel = $rel;
    }

    public function getDataType()
    {
        return $this->dataType;
    }

    public function getDescription()
    {
        return $this->description;
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

    public function getName()
    {
        return $this->name;
    }

    public function getRel()
    {
        return $this->rel;
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

    public function getTypeValidatorName()
    {
        return Parameter::getValidator($this->getDataType());
    }

    public function getValidationParameters()
    {
        return $this->validationParameters;
    }

    /**
     * @return boolean
     */
    public function hasSetter()
    {
        return ($this->getSetter() !== null);
    }
}
