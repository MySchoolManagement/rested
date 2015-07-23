<?php
namespace Rested\Definition;

use Rested\Exceptions\ImmutableException;
use Rested\Security\AccessVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ImmutableTransformMapping extends TransformMapping
{

    private $fieldsByOperation = [
        AccessVoter::ATTRIB_FIELD_GET => [],
        AccessVoter::ATTRIB_FIELD_SET => [],
    ];

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TransformMapping $transformMapping)
    {
        parent::__construct($transformMapping->getModelClass());

        $this->fieldsByOperation[AccessVoter::ATTRIB_FIELD_GET] = $this->filterFields(AccessVoter::ATTRIB_FIELD_GET);
        $this->fieldsByOperation[AccessVoter::ATTRIB_FIELD_SET] = $this->filterFields(AccessVoter::ATTRIB_FIELD_SET);
    }

    /**
     * {@inheritdoc}
     */
    public function addField($name, $dataType, $getter, $setter, $description, $validationParameters = null, $rel = null)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter($name, $dataType, $callable, $description)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addLink($routeName, $rel)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomFieldFilter($closure)
    {
        throw new ImmutableException();
    }

    protected function makeImmutableFields(AuthorizationCheckerInterface $authorizationChecker, $operation)
    {
        return array_map(function ($value) use ($authorizationChecker, $operation) {
            return $authorizationChecker->isGranted($operation, $value);
        }, $this->fields);

        return $this->runCustomFieldFilter($fields, $operation);
    }

    public function filterFiltersForAccess()
    {
        if ($this->cacheFilteredFiltersForAccess !== null) {
            $filters = $this->cacheFilteredFiltersForAccess;
        } else {
            //$authChecker = $this->getDefinition()->getResource()->getAuthorizationChecker();
            // FIXME:
            $authChecker = app('security.authorization_checker');

            $filters = array_filter(
                $this->filters,
                function ($filter) use ($authChecker) {
                    return $authChecker->isGranted(AccessVoter::ATTRIB_FILTER, $filter);
                }
            );

            $this->cacheFilteredFiltersForAccess = $filters;
        }

        return $filters;
    }

    public function runCustomFieldFilter(array $fields, $operation, $instance = null)
    {
        if ($this->customFieldFilter !== null) {
            $fields = call_user_func_array($this->customFieldFilter, [$this, $fields, $operation, $instance]);
        }

        return $fields;
    }
}
