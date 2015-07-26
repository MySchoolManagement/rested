<?php
namespace Rested;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;

interface FactoryInterface
{

    /**
     * @return \Rested\Http\CollectionResponse
     */
    public function createCollectionResponse(
        CompiledResourceDefinitionInterface $resourceDefinition,
        $href,
        array $items = [],
        $total = 0);

    /**
     * @return \Rested\Compiler\CompilerInterface
     */
    public function createCompiler($shouldApplyAccessControl = true);

    /**
     * @return \Rested\Compiler\CompilerCacheInterface
     */
    public function createCompilerCache();

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
    public function createResourceDefinition($name, $modelClass);

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
        $href,
        array $data,
        $instance = null);
}
