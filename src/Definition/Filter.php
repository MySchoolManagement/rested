<?php
namespace Rested\Definition;

class Filter
{

    private $callable;

    private $description;

    private $model;

    private $name;

    private $required;

    private $type;

    public function __construct(Model $model, $name, $callable, $description, $type)
    {
        $this->callable = $callable;
        $this->description = $description;
        $this->model = $model;
        $this->name = $name;
        $this->type = $type;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getType()
    {
        return $this->type;
    }
}
