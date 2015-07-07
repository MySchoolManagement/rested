<?php
namespace Rested;

use Rested\Definition\Model;
use Rested\Definition\ResourceDefinition;
use Rested\RestedResourceInterface;

interface FactoryInterface
{

    /**
     * @return Rested\RestedResourceInterface
     */
    public function createBasicController($class);

    /**
     * @return Rested\RestedResourceInterface
     */
    public function createBasicControllerFromRouteName($routeName);

    /**
     * @return Rested\CollectionResponse
     */
    public function createCollectionResponse(RestedResourceInterface $resource, array $items = [], $total = 0);

    /**
     * @return Rested\Definition\ResourceDefinition
     */
    public function createDefinition($name, RestedResourceInterface $resource, $class);

    /**
     * @return Rested\InstanceResponse
     */
    public function createInstanceResponse(RestedResourceInterface $resource, $href, $item);

    /**
     * return Rested\Definition\Model
     */
    public function createModel(ResourceDefinition $resourceDefinition, $class);
}