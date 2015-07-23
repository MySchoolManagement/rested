<?php
namespace Rested\Transforms;

class DefaultTransform implements TransformInterface
{



    public function export($instance, $expand = true, $forceAllFields = false)
    {
        $e = [];
        $resource = $this->resourceDefinition->getResource();
        $context = $forceAllFields ? null : $resource->getCurrentContext();
        $href = $resource->createInstanceHref($instance);

        if ($expand === true) {
            $fields = $this->filterFieldsForAccess(AccessVoter::ATTRIB_FIELD_GET);
            $isEloquent = $instance instanceof EloquentModel;

            foreach ($fields as $def) {
                // a null context means all fields except expansions
                if (($context === null) || ($forceAllFields === true) || ($context->wantsField($def->getName()) === true)) {
                    $callable = $def->getGetter();

                    if ($callable !== null) {
                        if (is_array($callable) === true) {
                            $val = call_user_func($callable, $instance);
                        } else {
                            if ($isEloquent === true) {
                                $val = $instance->getAttribute($callable);
                            } else {
                                $val = $instance->$callable();
                            }
                        }

                        $e[$def->getName()] = $this->exportValue($def, $val);
                    }
                }
            }
        }

        // FIXME
        return $resource->getFactory()->createInstanceResponse($resource, $href, $e, $instance);
    }

    public function exportAll($instance, $expand = true)
    {
        return $this->export($instance, $expand, true);
    }

    private function exportValue(Field $field, $value)
    {
        $return = null;

        if ((is_array($value) === true) || ($value instanceof \ArrayObject)) {
            $return = [];

            foreach ($value as $key => $otherValue) {
                $return[$key] = $this->exportValue($field, $otherValue);
            }
        } else if ($value instanceof \DateTime) {
            if ($field->getType() === Parameter::TYPE_DATE) {
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
    /**
     * Using the mapping information, applies the field data in $data
     * to $obj.
     *
     * @param string $locale
     *            Locale of the data to set.
     * @param array $data
     *            Data to apply.
     * @param object $obj
     *            Object to apply the mapping to. If this is null, a new
     *            instance is created.
     */
    public function apply($locale, array $data, $obj = null)
    {
        $definition = $this->resourceDefinition;
        $authChecker = $definition->getResource()->getAuthorizationChecker();

        if ($obj === null) {
            $class = $this->getDefiningClass();
            $obj = new $class();
        }

        if (method_exists($obj, 'setLocale') === true) {
            $obj->setLocale($locale);
        }

        // TODO: should we throw an exception when data is supplied for a field that cannot be set?
        foreach ($data as $key => $value) {
            if (($field = $this->findField($key)) !== null) {
                if ($field->isModel() === true) {
                    if ($authChecker->isGranted(AccessVoter::ATTRIB_FIELD_SET, $field) === false) {
                        continue;
                    }

                    $this->applyField($obj, $field, $value);
                }
            }
        }

        return $obj;
    }

    public function applyField($instance, Field $field, $value)
    {
        $isEloquent = $instance instanceof EloquentModel;
        $setter = $field->getSetter();

        if ($isEloquent === true) {
            $instance->setAttribute($setter, $value);
        } else {
            $instance->{$setter}($value);
        }
    }
    public function validate(array $data)
    {
        $rules = [];
        $messages = [];

        foreach ($this->filterFieldsForAccess(AccessVoter::ATTRIB_FIELD_SET) as $field) {
            if ($field->isModel() === true) {
                $parameters = $field->getValidationParameters();

                // add a validator for the data type of this field
                $parameters .= '|' . $field->getTypeValidatorName();

                $rules[$field->getName()] = $parameters;
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails() === true) {
            $failed = $validator->failed();
            $validationMessages = $validator->messages();;
            $messages = [];

            foreach ($failed as $field => $rules) {
                $messages[$field] = [];

                foreach ($rules as $rule => $parameters) {
                    $messages[$field][$rule] = $validationMessages->first($field);
                }
            }
        }

        return $messages;
    }
}
