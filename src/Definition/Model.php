<?php
namespace Rested\Definition;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Validator;
use Nocarrier\Hal;
use Rested\Helper;
use Rested\Http\InstanceResponse;
use Rested\Security\AccessVoter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Maps an external representation of a resource to an actual object.
 *
 * This is done through the use of getter and setter methods which exist on
 * the object.
 *
 * This allows for the creation of "virtual" resources that don't actually map
 * to any single object in the system.
 *
 * The fields added to a mapping are used to build the representation that is
 * sent to the client.
 *
 * For example:
 *
 * {
 * "name": "xyz"
 * "description": "a super description"
 * }
 */
class Model
{

    private $class;

    private $embeds = [];

    private $fields = [];

    private $filters = [];

    private $links = [];

    private $modelCache = [];

    private $primaryKeyField = 'uuid';

    private $resourceDefinition;

    private $customFieldFilter = null;

    private $cacheFilteredFieldsForAccess = [
        AccessVoter::ATTRIB_FIELD_GET => null,
        AccessVoter::ATTRIB_FIELD_SET => null,
    ];

    private $cacheFilteredFiltersForAccess = null;

    /**
     * Constructor
     *
     * @param string $class
     */
    public function __construct(ResourceDefinition $resourceDefinition, $class)
    {
        $this->class = $class;
        $this->resourceDefinition = $resourceDefinition;
    }

    /**
     * Adds a new field to the mapping.
     *
     * @param string $name
     *            The name of field. Used in the representation sent to the client.
     * @param string $type
     *            The data type of the field.
     * @param callable $getter
     *            Method to call on the resource to get the value.
     * @param callable $setter
     *            When applying a model, can this field be changed?
     * @param string $description
     *            Description of this field.
     * @param array $validationParameters
     *            List of validation settings.  These are symfony form settings.
     *
     * @return \Rested\Definition\InstanceDefinition Self
     */
    public function add($name, $type, $getter, $setter, $description, $validationParameters = null, $rel = null)
    {
        $this->fields[] = new Field($this, $name, $getter, $setter, $description, $type, $validationParameters, $rel);

        return $this;
    }

    public function addFilter($name, $type, $callable, $description)
    {
        $this->filters[] = new Filter($this, $name, $callable, $description, $type);

        return $this;
    }

    public function addLink($routeName, $rel)
    {
        $this->links[$rel] = $routeName;

        return $this;
    }

    public function addEmbed($name, $routeName, array $userData = [])
    {
        $this->embeds[] = new Embed($this, $name, $routeName, $userData);

        return $this;
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

    public function filterEmbedsForAccess()
    {
        return $this->embeds;
    }

    public function filterFieldsForAccess($operation)
    {
        if ($this->cacheFilteredFieldsForAccess[$operation] !== null) {
            $fields = $this->cacheFilteredFieldsForAccess[$operation];
        } else {
            $authChecker = $this->getDefinition()->getResource()->getAuthorizationChecker();
            $fields = array_filter(
                $this->fields,
                function ($field) use ($authChecker, $operation) {
                    return $authChecker->isGranted($operation, $field);
                }
            );

            $this->cacheFilteredFieldsForAccess[$operation] = $fields;
        }

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

    public function findField($name)
    {
        foreach ($this->fields as $field) {
            if (strcasecmp($name, $field->getName()) === 0) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return \Rested\Definition\Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return \Rested\Definition\Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public function getDefinition()
    {
        return $this->resourceDefinition;
    }

    /**
     * @return \Rested\Definition\Field|null
     */
    public function getPrimaryKeyField()
    {
        return $this->findField($this->primaryKeyField);
    }

    public function getPrimaryKeyValueForInstance($instance)
    {
        if (($field = $this->getPrimaryKeyField()) !== null) {
            $isEloquent = $instance instanceof EloquentModel;

            if ($isEloquent === true) {
                return $instance->getAttribute($field->getGetter());
            }

            return $instance->{$field->getGetter()}();
        }

        return null;
    }

    private function getUser()
    {
        $resource = $this->resourceDefinition->getResource();
        $user = $resource->getUser();

        return $user;
    }

    /**
     * Adds a new field to the mapping.
     *
     * @param string $name
     *            The name of field. Used in the representation sent to the client.
     * @param string $type
     *            The data type of the field.
     * @param callable $getter
     *            Method to call on the resource to get the value.
     * @param callable $setter
     *            When applying a model, can this field be changed?
     * @param string $description
     *            Description of this field.
     * @param array $validationParameters
     *            List of validation settings.  These are symfony form settings.
     *
     * @return \Rested\Definition\InstanceDefinition Self
     */
    public function setField($name, $type, $getter, $setter, $description, $validationParameters = null, $rel = null)
    {
        $this->add($name, $type, $getter, $setter, $description, $validationParameters, $rel);

        return $this;
    }

    public function export($instance, $forceAllFields = false)
    {
        $e = [];
        $resource = $this->resourceDefinition->getResource();
        $context = $forceAllFields ? null : $resource->getCurrentContext();
        $href = $resource->createInstanceHref($instance);

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

        $response = $resource->getFactory()->createInstanceResponse($resource, $href, $e, $instance);
        $this->exportEmbeds($response, $instance);

        return $response;
    }

    public function exportEmbeds(InstanceResponse $response, $instance)
    {
        $models = [

        ];

        $embeds = $this->filterEmbedsForAccess();
        $resource = $this->resourceDefinition->getResource();
        $context = $resource->getCurrentRequest() ? $resource->getCurrentContext() : null;

        foreach ($embeds as $embed) {
            $name = $embed->getName();

            if (($context === null) || ($context->wantsEmbeddable($name) === false)) {
                continue;
            }

            if (array_key_exists($name, $models) === false) {
                // FIXME: provide a nicer way to do this
                $this->modelCache[$name] = app($embed->getRouteName())
                    ->getDefinition()
                    ->findAction(ActionDefinition::TYPE_INSTANCE)
                    ->getModel()
                ;
            }

            $model = $this->modelCache[$name];
            $values = $instance->getAttribute($embed->getUserData()['rel']);

            if ($values === null) {
                // $response->addResource($name, null, false);
            } else if ($values instanceof Collection) {
                $items = [];

                foreach ($values as $value) {
                    $items[] = $model->exportAll($value);
                }

                $response->addResource($name, $resource->getFactory()->createCollectionResponse($resource, $items), false);
            } else {
                $response->addResource($name, $model->exportAll($values), false);
            }
        }
    }

    public function exportAll($instance)
    {
        return $this->export($instance, true);
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

    public function getDefiningClass()
    {
        return $this->class;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function runCustomFieldFilter(array $fields, $operation, $instance = null)
    {
        if ($this->customFieldFilter !== null) {
            $fields = call_user_func_array($this->customFieldFilter, [$this, $fields, $operation, $instance]);
        }

        return $fields;
    }

    public function setCustomFieldFilter($closure)
    {
        $this->customFieldFilter = $closure;
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
            $messages = Helper::makeValidationMessages($validator);
        }

        return $messages;
    }
}
