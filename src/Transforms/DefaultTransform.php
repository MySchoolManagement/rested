<?php
namespace Rested\Transforms;

use Rested\Compiler\CompilerCacheInterface;
use Rested\Compiler\CompilerHelper;
use Rested\Definition\ActionDefinition;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\Definition\Embed;
use Rested\Definition\Field;
use Rested\Definition\GetterField;
use Rested\Definition\Parameter;
use Rested\Definition\SetterField;
use Rested\FactoryInterface;
use Rested\Helper;
use Rested\Http\ContextInterface;
use Rested\Http\EmbedContext;
use Rested\Http\InstanceResponse;
use Rested\ResourceInterface;
use Rested\UrlGeneratorInterface;

class DefaultTransform implements TransformInterface, \Serializable
{

    /**
     * List of transforms to hydrate, this is when loading a set of transforms from the cache.
     *
     * @var DefaultTransform[]
     */
    static $hydrate = [];

    /**
     * @var \Rested\Compiler\CompilerCacheInterface
     */
    protected $compilerCache;

    /**
     * @var \Rested\FactoryInterface
     */
    protected $factory;

    /**
     * @var \Rested\UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * {@inheritdoc}
     */
    public function apply(CompiledTransformMappingInterface $transformMapping, $locale, array $input, $instance = null)
    {
        if ($instance === null) {
            $modelClass = $transformMapping->getModelClass();
            $instance = new $modelClass();
        }

        if (method_exists($instance, 'setLocale') === true) {
            $instance->setLocale($locale);
        }

        // FIXME: we should filter out fields when data is supplied for a field that cannot be set?
        foreach ($input as $key => $value) {
            if (($field = $transformMapping->findField($key, SetterField::OPERATION)) !== null) {
                $this->applyField($transformMapping, $instance, $field, $value);
            }
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function applyField(CompiledTransformMappingInterface $transformMapping, $instance, Field $field, $value)
    {
        $callback = $field->getCallback();
        $this->setFieldValue($instance, $callback, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function export(ContextInterface $context, ResourceInterface $resource, CompiledTransformMappingInterface $transformMapping, $instance)
    {
        return $this->exportModel($context, $resource, $transformMapping, $instance);
    }

    /**
     * {@inheritdoc}
     */
    public function exportAll(ContextInterface $context, ResourceInterface $resource, CompiledTransformMappingInterface $transformMapping, $instance)
    {
        return $this->exportModel($context, $resource, $transformMapping, $instance, true);
    }

    protected function exportEmbeds(
        CompiledTransformMappingInterface $transformMapping,
        ContextInterface $context,
        InstanceResponse $response,
        $instance)
    {
        $embeds = $transformMapping->getEmbeds();

        foreach ($embeds as $embed) {
            $name = $embed->getName();

            if (($context === null) || ($context->wantsEmbed($name) === false)) {
                continue;
            }

            $object = $this->exportEmbed($context, $response->getResource(), $embed, $instance);

            if ($object !== null) {
                $response->addResource($name, $object, false);
            }
        }
    }

    protected function exportEmbed(ContextInterface $context, ResourceInterface $resource, Embed $embed, $instance)
    {
        $routeName = $embed->getRouteName();
        $embedResourceDefinition = $this->compilerCache->findResourceDefinition($routeName);

        if ($embedResourceDefinition !== null) {
            $embedAction = $embedResourceDefinition->findActionByRouteName($routeName);

            if ($embedAction !== null) {
                $embedTransform = $embedAction->getTransform();
                $embedTransformMapping = $embedAction->getTransformMapping();
                $embedContext = EmbedContext::create($embedResourceDefinition, $context, $embed->getName());

                $value = $this->getEmbedValue($embedTransform, $embedTransformMapping, $embed, $instance);

                if ($value === null) {

                } else {
                    if ((is_array($value) === true) || (is_a($value, '\Traversable') === true)) {
                        $items = [];

                        foreach ($value as $item) {
                            $export = $embedTransform->export($embedContext, $resource, $embedTransformMapping, $item);
                            $items[] = $export;
                        }

                        return $this->factory->createCollectionResponse($embedResourceDefinition, $resource, $context, null, $items);
                    } else {
                        return $embedTransform->export($embedContext, $resource, $embedTransformMapping, $value);
                    }
                }
            }
        }

        return null;
    }

    protected function exportModel(
        ContextInterface $context = null,
        ResourceInterface $resource,
        CompiledTransformMappingInterface $transformMapping,
        $instance,
        $allFields = false)
    {
        $item = [];
        $resourceDefinition = $this->compilerCache->findResourceDefinitionForModelClass(get_class($instance));
        $href = $this->makeUrlForInstance($resourceDefinition, $instance);

        $fields = $transformMapping->getFields(GetterField::OPERATION);

        foreach ($fields as $def) {
            if (($allFields === true) || ($context->wantsField($def->getName()) === true)) {
                $callable = $def->getCallback();

                if ($callable !== null) {
                    if (is_array($callable) === true) {
                        $val = call_user_func($callable, $instance);
                    } else {
                        $val = $this->getFieldValue($instance, $callable);
                    }

                    $item[$def->getName()] = $this->exportValue($def, $val);
                }
            }
        }

        $response = $this->factory->createInstanceResponse($resourceDefinition, $resource, $context, $href, $item, $instance);
        $this->exportEmbeds($transformMapping, $context, $response, $instance);

        return $response;
    }

    protected function exportValue(Field $field, $value)
    {
        $return = null;

        if ((is_array($value) === true) || ($value instanceof \ArrayObject)) {
            $return = [];

            foreach ($value as $key => $otherValue) {
                $return[$key] = $this->exportValue($field, $otherValue);
            }
        } else if ($value instanceof \DateTime) {
            if ($field->getDataType() === Parameter::TYPE_DATE) {
                $return = $value->format('Y-m-d');
            } else {
                $return = $value->format(\DateTime::ISO8601);
            }
        } else if (is_object($value) === true) {
            $ret = [];

            foreach ($value as $k => $v) {
                $ret[Helper::toUnderscore($k)] = $v;
            }

            return $ret;
        } else {
            $return = $value;
        }

        return $return;
    }

    protected function getEmbedValue(
        TransformInterface $transform,
        CompiledTransformMappingInterface $transformMapping,
        Embed $embed,
        $instance)
    {
        $userData = $embed->getUserData();

        if (array_key_exists('method', $userData) === true) {
            return $instance->{$userData['method']}();
        }

        return null;
    }

    protected function getFieldValue($instance, $callable)
    {
        return $instance->{$callable}();
    }

    protected function makeUrlForInstance(CompiledResourceDefinitionInterface $resourceDefinition, $instance)
    {
        $action = $resourceDefinition->findFirstAction(ActionDefinition::TYPE_INSTANCE);

        return $this->urlGenerator->route($action->getRouteName(), [
            'id' => $this->retrieveIdFromInstance($action->getTransformMapping(), $instance),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveIdFromInstance(CompiledTransformMappingInterface $transformMapping, $instance)
    {
        $primaryField = $transformMapping->findPrimaryKeyField();

        if ($primaryField !== null) {
            return $this->getFieldValue($instance, $primaryField->getCallback());
        }

        return null;
    }

    protected function setFieldValue($instance, $callback, $value)
    {
        $instance->{$callback}($value);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(CompiledTransformMappingInterface $transformMapping, array $input)
    {
        return true;
    }

    public function serialize()
    {
        return CompilerHelper::serialize(get_object_vars($this), ['compilerCache', 'factory', 'urlGenerator']);
    }

    public function unserialize($data)
    {
        foreach (unserialize($data) as $k => $v) {
            $this->{$k} = $v;
        }

        static::$hydrate[] = $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setServices(FactoryInterface $factory, CompilerCacheInterface $compilerCache, UrlGeneratorInterface $urlGenerator)
    {
        $this->compilerCache = $compilerCache;
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;

        return $this;
    }
}
