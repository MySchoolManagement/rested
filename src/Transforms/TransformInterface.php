<?php
namespace Rested\Transforms;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\Definition\Field;
use Rested\Http\ContextInterface;

interface TransformInterface
{

    public function apply(CompiledTransformMappingInterface $transformMapping, $locale, array $input, $instance = null);

    public function applyField(CompiledTransformMappingInterface $transformMapping, $instance, Field $field, $value);

    public function export(ContextInterface $context = null, CompiledTransformMappingInterface $transformMapping, $instance);

    public function exportAll(ContextInterface $context = null, CompiledTransformMappingInterface $transformMapping, $instance);

    public function retrieveIdFrominstance(CompiledTransformMappingInterface $transformMapping, $instance);

    public function validate(CompiledTransformMappingInterface $transformMapping, array $input);
}
