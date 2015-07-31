<?php
namespace Rested\Definition;

use Rested\Transforms\TransformMappingInterface;

abstract class Field
{

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
    public $name;

    /**
     * @var string
     */
    protected $rel;

    public function __construct($name, $callback, $description, $dataType, $rel = null)
    {
        $this->callback = $callback;
        $this->dataType = $dataType;
        $this->description = $description;
        $this->name = $name;
        $this->rel = $rel;
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

    /**
     * @return null|string
     */
    public function getRel()
    {
        return $this->rel;
    }
}
