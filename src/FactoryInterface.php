<?php
namespace Rested;

use Rested\Definition\ResourceDefinition;

interface FactoryInterface
{

    /**
     * @return \Rested\Http\CollectionResponse
     */
    public function createCollectionResponse(RestedResourceInterface $resource, array $items = [], $total = 0);

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public function createDefinition($name, $modelClass);

    /**
     * @return \Rested\Http\ImmutableContext
     */
    public function createImmutableContext(array $parameters, $actionType, $routeName, ResourceDefinition $resourceDefinition);

    /**
     * @return \Rested\Http\InstanceResponse
     */
    public function createInstanceResponse(RestedResourceInterface $resource, $href, $item, $instance = null);

    /**
     * return \Rested\Definition\TransformMapping
     */
    public function createTransformMapping($class);
}
