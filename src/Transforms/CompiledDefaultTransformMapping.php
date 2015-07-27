<?php
namespace Rested\Transforms;

use Rested\Definition\Filter;
use Rested\Definition\GetterField;
use Rested\Definition\SetterField;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompiledDefaultTransformMapping extends DefaultTransformMapping implements CompiledTransformMappingInterface
{

    /**
     * @var bool
     */
    protected $accessControlApplied = false;

    public function __construct($modelClass, $primaryKeyFieldName, $fields, $filters, $links, $fieldFilterCallback = null)
    {
        $this->fieldsByOperation = $fields;
        $this->fieldFilterCallback = $fieldFilterCallback;
        $this->filters = $filters;
        $this->links = $links;
        $this->modelClass = $modelClass;
        $this->primaryKeyFieldName = $primaryKeyFieldName;
    }

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @return void
     */
    public function applyAccessControl(AuthorizationCheckerInterface $authorizationChecker)
    {
        if ($this->accessControlApplied === false) {
            $this->filterFields($authorizationChecker, GetterField::OPERATION, GetterField::SECURITY_ATTRIBUTE);
            $this->filterFields($authorizationChecker, SetterField::OPERATION, SetterField::SECURITY_ATTRIBUTE);
            $this->filterFilters($authorizationChecker);

            $this->accessControlApplied = true;
        }
    }

    /**
     * @return void
     */
    protected function filterFields(AuthorizationCheckerInterface $authorizationChecker, $operation, $securityAttribute)
    {
        $this->fieldsByOperation[$operation] = array_filter(
            $this->fieldsByOperation[$operation],
            function ($value) use ($authorizationChecker, $securityAttribute) {
                return $authorizationChecker->isGranted($securityAttribute, $value);
            }
        );
    }

    /**
     * @return void
     */
    protected function filterFilters(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->filters = array_filter(
            $this->filters,
            function ($value) use($authorizationChecker) {
                return $authorizationChecker->isGranted(Filter::SECURITY_ATTRIBUTE, $value);
            }
        );
    }
}
