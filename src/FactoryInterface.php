<?php
namespace Rested;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\Http\ContextInterface;

interface FactoryInterface
{

    /**
     * @return \Rested\Http\CollectionResponse
     */
    public function createCollectionResponse(
        CompiledResourceDefinitionInterface $resourceDefinition,
        ContextInterface $context,
        $href,
        array $items = [],
        $total = null);

    /**
     * @return \Rested\Http\ContextInterface
     */
    public function createContext(
        array $parameters,
        $actionType,
        $routeName,
        CompiledResourceDefinitionInterface $resourceDefinition);

    /**
     * @return \Rested\Definition\ResourceDefinitionInterface
     */
    public function createResourceDefinition($name, $controllerClass, $modelClass);

    /**
     * @return \Rested\Transforms\TransformMappingInterface
     */
    public function createTransform();

    /**
     * @return \Rested\Transforms\TransformMappingInterface
     */
    public function createTransformMapping($modelClass);

    /**
     * @return \Rested\Http\InstanceResponse
     */
    public function createInstanceResponse(
        CompiledResourceDefinitionInterface $resourceDefinition,
        ContextInterface $context,
        $href,
        array $data,
        $instance = null);
}
