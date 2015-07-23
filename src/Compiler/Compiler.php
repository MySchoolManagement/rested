<?php
namespace Rested\Compiler;

use Rested\Definition\ActionDefinition;
use Rested\Definition\ActionDefinitionInterface;
use Rested\Definition\Compiled\CompiledActionDefinition;
use Rested\Definition\Compiled\CompiledEmbed;
use Rested\Definition\Compiled\CompiledFilter;
use Rested\Definition\Compiled\CompiledGetterField;
use Rested\Definition\Compiled\CompiledResourceDefinition;
use Rested\Definition\Compiled\CompiledSetterField;
use Rested\Definition\Embed;
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

class Compiler implements CompilerInterface
{

    /**
     * @var \Rested\Transforms\CompiledTransformMappingInterface[]
     */
    protected $transformMappingCache = [];

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
        NameGeneratorInterface $nameGenerator,
        UrlGeneratorInterface $urlGenerator,
        $shouldApplyAccessControl = true)
    {
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
        $actions = $this->compileActionDefinitions($resourceDefinition->getActions(), $path);
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
    protected function compileActionDefinitions(array $actions, $path)
    {
        return array_map(
            function($value) use ($path) {
                return $this->compileActionDefinition($value, $path);
            },
            $actions
        );
    }

    protected function compileEmbeds(array $embeds, $path)
    {
        return array_map(
            function($value) use($path) {
                return $this->compileEmbed($value, $path);
            },
            $embeds
        );
    }

    protected function compileEmbed(Embed $embed, $path)
    {
        return new CompiledEmbed(
            $this->nameGenerator->rolesForEmbed($embed, $path),
            $embed->getName(),
            $embed->getRouteName(),
            $embed->getUserData()
        );
    }

    protected function compileFields(array $fields, $path)
    {
        return array_map(
            function($value) use ($path) {
                if (is_a($value, GetterField::class) === true) {
                    return $this->compileGetterField($value, $path);
                } else {
                    return $this->compileSetterField($value, $path);
                }
            },
            $fields
        );
    }

    protected function compileFilters(array $filters, $path)
    {
        return array_map(
            function($value) use($path) {
                return $this->compileFilter($value, $path);
            },
            $filters
        );
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
        $compilerId = $transformMapping->getCompilerId();

        if (array_key_exists($compilerId, $this->transformMappingCache) === true) {
            return $this->transformMappingCache[$compilerId];
        }

        $fields = [
            GetterField::OPERATION =>
                $this->compileFields($transformMapping->getFields(GetterField::OPERATION), $path),
            SetterField::OPERATION =>
                $this->compileFields($transformMapping->getFields(SetterField::OPERATION), $path),
        ];
        $filters = $this->compileFilters($transformMapping->getFilters(), $path);
        $embeds = $this->compileEmbeds($transformMapping->getEmbeds(), $path);

        $compiledTransformMapping = new CompiledDefaultTransformMapping(
            $transformMapping->getModelClass(),
            $transformMapping->getPrimaryKeyFieldName(),
            $embeds,
            $fields,
            $filters,
            $transformMapping->getLinks(),
            $transformMapping->getFieldFilterCallback()
        );

        return $this->transformMappingCache[$compilerId] = $compiledTransformMapping;
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
