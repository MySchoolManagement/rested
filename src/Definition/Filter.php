<?php
namespace Rested\Definition;

class Filter
{

    const SECURITY_ATTRIBUTE = 'rested_filter';

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $dataType;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $name;

    public function __construct($name, $callback, $description, $dataType)
    {
        $this->callback = $callback;
        $this->dataType = $dataType;
        $this->description = $description;
        $this->name = $name;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
