<?php
namespace Rested\Transforms;

class CompiledDefaultTransformMapping extends DefaultTransformMapping implements CompiledTransformMappingInterface
{

    public function __construct($modelClass, $primaryKeyFieldName, $fields, $filters, $links, $fieldFilterCallback = null)
    {
        $this->fieldsByOperation = $fields;
        $this->fieldFilterCallback = $fieldFilterCallback;
        $this->filters = $filters;
        $this->links = $links;
        $this->modelClass = $modelClass;
        $this->primaryKeyFieldName = $primaryKeyFieldName;
    }
}
