<?php
namespace Rested\Compiler;

use Rested\Definition\ActionDefinition;
use Rested\Definition\ActionDefinitionInterface;
use Rested\Definition\Compiled\CompiledActionDefinition;
use Rested\Definition\Compiled\CompiledFilter;
use Rested\Definition\Compiled\CompiledFilterField;
use Rested\Definition\Compiled\CompiledGetterField;
use Rested\Definition\Compiled\CompiledResourceDefinition;
use Rested\Definition\Compiled\CompiledSetterField;
use Rested\Definition\Field;
use Rested\Definition\Filter;
use Rested\Definition\GetterField;
use Rested\Definition\Parameter;
use Rested\Definition\ResourceDefinitionInterface;
use Rested\Definition\SetterField;
use Rested\FactoryInterface;
use Rested\NameGeneratorInterface;
use Rested\Transforms\CompiledDefaultTransformMapping;
use Rested\Transforms\TransformMappingInterface;
use Rested\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Compiler implements CompilerInterface
{

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Rested\FactoryInterface
     */
    protected $factory;

    /**
     * @var \Rested\NameGeneratorInterface
     */
    protected $nameGenerator;

    /**
     * @var \Rested\UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var bool
     */
    protected $shouldApplyAccessControl;

    public function __construct(
        FactoryInterface $factory,
        AuthorizationCheckerInterface $authorizationChecker,
        NameGeneratorInterface $nameGenerator,
        UrlGeneratorInterface $urlGenerator,
        $shouldApplyAccessControl = true)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->factory = $factory;
        $this->nameGenerator = $nameGenerator;
        $this->shouldApplyAccessControl = $shouldApplyAccessControl;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(ResourceDefinitionInterface $resourceDefinition)
    {
        $path = $resourceDefinition->getPath();
        $actions = $this->compileAndFilterActionDefinitions($resourceDefinition->getActions(), $path);
        $defaultTransformMapping = $this->compileTransformMapping($resourceDefinition->getDefaultTransformMapping(), $path);

        return new CompiledResourceDefinition(
            $this->factory,
            $resourceDefinition->getPath(),
            $resourceDefinition->getName(),
            $resourceDefinition->getSummary(),
            $resourceDefinition->getDescription(),
            $actions,
            $resourceDefinition->getDefaultTransform(),
            $defaultTransformMapping
        );
    }

    /**
     * @return \Rested\Definition\Compiled\CompiledActionDefinitionInterface
     */
    protected function compileActionDefinition(ActionDefinitionInterface $actionDefinition, $path)
    {
        return new CompiledActionDefinition(
            $this->nameGenerator->routeName($actionDefinition, $path),
            $this->nameGenerator->rolesForAction($actionDefinition, $path),
            $this->generateUrlForAction($actionDefinition, $path, true),
            $this->generateUrlForAction($actionDefinition, $path, false),
            $actionDefinition->getType(),
            $actionDefinition->getId(),
            $actionDefinition->shouldAppendId(),
            $actionDefinition->getSummary(),
            $actionDefinition->getDescription(),
            $actionDefinition->getControllerName(),
            $actionDefinition->getHttpMethod(),
            $actionDefinition->getAcceptedContentType(),
            $actionDefinition->getAffordanceAvailabilityCallback(),
            $actionDefinition->getTokens(),
            $actionDefinition->getTransform(),
            $this->compileTransformMapping($actionDefinition->getTransformMapping(), $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function compileAndFilterActionDefinitions(array $actions, $path)
    {
        $actions = array_map(
            function($value) use ($path) {
                return $this->compileActionDefinition($value, $path);
            },
            $actions
        );

        return $this->filterActionDefinitions($actions);
    }

    protected function compileAndFilterFields(array $fields, $securityAttribute, $path)
    {
        $fields = array_map(
            function($value) use ($path) {
                if (is_a($value, GetterField::class) === true) {
                    return $this->compileGetterField($value, $path);
                } else {
                    return $this->compileSetterField($value, $path);
                }
            },
            $fields
        );

        return $this->filterFields($fields, $securityAttribute);
    }

    protected function compileAndFilterFilters(array $filters, $path)
    {
        $filters = array_map(
            function($value) use($path) {
                return $this->compileFilter($value, $path);
            },
            $filters
        );

        return $this->filterFilters($filters);
    }

    protected function compileFilter(Filter $filter, $path)
    {
        return new CompiledFilter(
            $this->nameGenerator->rolesForFilter($filter, $path),
            $filter->getName(),
            $filter->getCallback(),
            $filter->getDescription(),
            $filter->getDataType()
        );
    }

    protected function compileGetterField(GetterField $field, $path)
    {
        return new CompiledGetterField(
            $this->nameGenerator->rolesForField($field, GetterField::SECURITY_ATTRIBUTE, $path),
            $field->getName(),
            $field->getCallback(),
            $field->getDescription(),
            $field->getDataType(),
            $field->getRel()
        );
    }

    protected function compileSetterField(SetterField $field, $path)
    {
        return new CompiledSetterField(
            $this->nameGenerator->rolesForField($field, SetterField::SECURITY_ATTRIBUTE, $path),
            $field->getName(),
            $field->getCallback(),
            $field->getDescription(),
            $field->getDataType(),
            $field->getRel(),
            $field->getValidationParameters()
        );
    }

    /**
     * @return \Rested\Transforms\CompiledTransformMappingInterface
     */
    protected function compileTransformMapping(TransformMappingInterface $transformMapping, $path)
    {
        $fields = [
            GetterField::OPERATION =>
                $this->compileAndFilterFields($transformMapping->getFields(GetterField::OPERATION), GetterField::SECURITY_ATTRIBUTE, $path),
            SetterField::OPERATION =>
                $this->compileAndFilterFields($transformMapping->getFields(SetterField::OPERATION), SetterField::SECURITY_ATTRIBUTE, $path),
        ];
        $filters = $this->compileAndFilterFilters($transformMapping->getFilters(), $path);

        return new CompiledDefaultTransformMapping(
            $transformMapping->getModelClass(),
            $transformMapping->getPrimaryKeyFieldName(),
            $fields,
            $filters,
            $transformMapping->getLinks(),
            $transformMapping->getFieldFilterCallback()
        );
    }

    /**
     * @return \Rested\Definition\ActionDefinitionInterface[]
     */
    protected function filterActionDefinitions(array $actions)
    {
        if ($this->shouldApplyAccessControl === false) {
            return $actions;
        }

        return array_filter(
            $actions,
            function ($value) {
                return $this->authorizationChecker->isGranted(ActionDefinition::SECURITY_ATTRIBUTE, $value);
            }
        );
    }

    /**
     * @return \Rested\Definition\Field[]
     */
    protected function filterFields(array $fields, $securityAttribute)
    {
        if ($this->shouldApplyAccessControl === false) {
            return $fields;
        }

        return array_filter(
            $fields,
            function ($value) use ($securityAttribute) {
                return $this->authorizationChecker->isGranted($securityAttribute, $value);
            }
        );
    }

    /**
     * @return \Rested\Definition\Filter[]
     */
    protected function filterFilters(array $filters)
    {
        if ($this->shouldApplyAccessControl === false) {
            return $filters;
        }

        return array_filter(
            $filters,
            function ($value) {
                return $this->authorizationChecker->isGranted(Filter::SECURITY_ATTRIBUTE, $value);
            }
        );
    }

    /**
     * Generates a Url for an action.
     *
     * @param string $path The path for the resource the action belongs to.
     * @param \Rested\Definition\ActionDefinitionInterface $action Action to generate a Url for.
     * @param bool $absolute Should the Url be absolute?
     *
     * @return string
     */
    protected function generateUrlForAction(ActionDefinitionInterface $action, $path, $absolute = true)
    {
        $tokens = $action->getTokens();
        $components = array_map(function(Parameter $value) {
            return sprintf('{%s}', $value->getName());
        }, $tokens);

        if ($action->shouldAppendId() === true) {
            $components[] = $action->getId();
        }

        array_unshift($components, $path);
        array_unshift($components, $this->urlGenerator->getMountPath());

        $u = join('/', $components);
        $u = preg_replace('/\/{2,}/', '/', $u);

        return $this->urlGenerator->url($u, $absolute);
    }
}
