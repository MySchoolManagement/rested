<?php
namespace Rested\Definition;

class Filter
{

    private $callable;

    private $dataType;

    private $description;

    private $name;

    public function __construct($name, $callable, $description, $dataType)
    {
        $this->callable = $callable;
        $this->dataType = $dataType;
        $this->description = $description;
        $this->name = $name;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getDataType()
    {
        return $this->dataType;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getName()
    {
        return $this->name;
    }
}
