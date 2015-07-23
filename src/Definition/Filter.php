<?php
namespace Rested\Definition;

use Rested\Transforms\TransformMappingInterface;

class Filter
{

    const SECURITY_ATTRIBUTE = 'rested_filter';

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $name;

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
