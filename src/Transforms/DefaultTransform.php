<?php
namespace Rested\Transforms;

use Rested\Definition\ActionDefinition;
use Rested\Definition\Compiled\CompiledActionDefinitionInterface;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\Definition\Field;
use Rested\Definition\GetterField;
use Rested\Definition\Parameter;
use Rested\Definition\ResourceDefinitionInterface;
use Rested\Definition\SetterField;
use Rested\FactoryInterface;
use Rested\Http\ContextInterface;
use Rested\UrlGeneratorInterface;

class DefaultTransform implements TransformInterface
{

    /**
     * @var \Rested\FactoryInterface
     */
    protected $factory;

    /**
     * @var \Rested\UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(FactoryInterface $factory, UrlGeneratorInterface $urlGenerator)
    {
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
    }

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
    public function export(ContextInterface $context, CompiledTransformMappingInterface $transformMapping, $instance)
    {
        return $this->exportModel($context, $transformMapping, $instance);
    }

    /**
     * {@inheritdoc}
     */
    public function exportAll(ContextInterface $context, CompiledTransformMappingInterface $transformMapping, $instance)
    {
        return $this->exportModel($context, $transformMapping, $instance, true);
    }

    protected function exportModel(
        ContextInterface $context,
        TransformMappingInterface $transformMapping,
        $instance,
        $allFields = false)
    {
        $item = [];
        $resourceDefinition = $context->getResourceDefinition();
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

        return $this->factory->createInstanceResponse($resourceDefinition, $href, $item, $instance);
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
            throw new \Exception('NOT IMPLEMENTED');
            //$return = $value->export($context);
        } else {
            $return = $value;
        }

        return $return;
    }

    protected function getFieldValue($instance, $callable)
    {
        return $instance->{$callable}();
    }

    /**
     * {@inheritdoc}
     */
    public function makeUrlForInstance(CompiledResourceDefinitionInterface $resourceDefinition, $instance)
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
}
