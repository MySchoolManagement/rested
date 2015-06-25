<?php
namespace Rested\Definition;

use Rested\Helper;

class Field
{

    private $cacheRoleNames;
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

    public function getRoleNames($operation)
    {
        if ($this->cacheRoleNames !== null) {
            return $this->cacheRoleNames;
        }

        $endpoint = $this->getModel()->getDefinition()->getEndpoint();

        $roles = [
            Helper::makeRoleName($endpoint, 'field', $this->getName()),
            Helper::makeRoleName($endpoint, 'field', $this->getName(), $operation),
            Helper::makeRoleName($endpoint, 'field', 'all'),
            Helper::makeRoleName($endpoint, 'field', 'all', $operation),
        ];


        return ($this->cacheRoleName = $roles);
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
