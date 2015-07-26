<?php
namespace Rested\Definition;

class SetterField extends Field
{

    const OPERATION = 'set';

    const SECURITY_ATTRIBUTE = 'rested_field_set';

    /**
     * @var string[]
     */
    private $validationParameters = [];

    public function __construct($name, $callback, $description, $dataType, $rel = null, $validationParameters = [])
    {
        parent::__construct($name, $callback, $description, $dataType, $rel);

        $this->validationParameters = $validationParameters;
    }

    /**
     * @return null|string
     */
    public function getTypeValidatorName()
    {
        return Parameter::getValidator($this->getDataType());
    }

    /**
     * @return string[]
     */
    public function getValidationParameters()
    {
        return $this->validationParameters;
    }
}
