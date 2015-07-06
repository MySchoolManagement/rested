<?php
namespace Rested;

use Rested\Definition\Model;
use Rested\Definition\ResourceDefinition;

interface FactoryInterface
{

    /**
     * @return Rested\RestedResourceInterface
     */
    public function createBasicController($class);

    /**
     * @return Rested\Definition\ResourceDefinition
     */
    public function createDefinition($name, RestedResourceInterface $resource, $class);

    /**
     * return Rested\Definition\Model
     */
    public function createModel(ResourceDefinition $resourceDefinition, $class);
}