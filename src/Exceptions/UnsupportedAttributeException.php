<?php
namespace Rested\Exceptions;

class UnsupportedAttributeException extends \Exception
{

    private $attribute;
    private $class;

    public function __construct($attribute, $class, $code = 0, Exception $previous = null)
    {
        $this->attribute = $attribute;
        $this->class = $class;

        parent::__construct(sprintf('The attribute "%s" is not supported on an instance of "%s"', $attribute, $class), $code, $previous);
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getClass()
    {
        return $this->class;
    }
}