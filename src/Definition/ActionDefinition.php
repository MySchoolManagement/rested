<?php
namespace Rested\Definition;

use Rested\Helper;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ActionDefinition
{

    const TYPE_COLLECTION = 'collection';
    const TYPE_CREATE = 'create';
    const TYPE_DELETE = 'delete';
    const TYPE_INSTANCE = 'instance';
    const TYPE_INSTANCE_AFFORDANCE = 'instance_affordance';
    const TYPE_UPDATE = 'update';

    private $affordanceChecker;

    /**
     * @var \Rested\Definition\ResourceDefinition
     */
    private $definition;

    private $acceptedContentTypes = [
        'application/json'
    ];

    private $controllerName;

    private $description;

    private $id;

    private $method;

    /**
     * @var null|\Rested\Definition\TransformMapping
     */
    private $transformMapping;

    /**
     * @var bool
     */
    private $shouldAppendId;

    private $summary;

    private $type;

    /**
     * @var \Rested\Definition\Parameter[]
     */
    private $tokens = [];

    /**
     * Constructs a new ActionDefinition.
     *
     * @param \Rested\Definition\ResourceDefinition $definition Resource the action is available on.
     * @param string $type Type of action to add on this resource.
     * @param $id
     */
    public function __construct(ResourceDefinition $definition, $type, $id)
    {
        // convert hyphenated to camelcase
        $this->controllerName = preg_replace_callback(
            '!\-[a-zA-Z]!',
            function ($matches) {
                return strtoupper(str_replace('-', '', $matches[0]));
            },
            $id
        );

        $this->definition = $definition;
        $this->id = $id;
        $this->type = $type;
        $this->method = self::methodFromType($type);
    }

    public function addToken($name, $type, $defaultValue = null, $description = null)
    {
        $this->tokens[] = new Parameter($name, $type, $defaultValue, $description);

        return $this;
    }

    public function checkAffordance($instance = null)
    {
        // TODO: refactor
        if ($this->affordanceChecker === null) {
            return true;
        }

        return call_user_func_array($this->affordanceChecker, [$instance]);
    }

    public function getAcceptedContentTypes()
    {
        return $this->acceptedContentTypes;
    }

    public function getControllerName()
    {
        return $this->controllerName;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return \Rested\Definition\TransformMapping
     */
    public function getTransformMapping()
    {
        if ($this->transformMapping !== null) {
            return $this->transformMapping;
        }

        return $this->getDefinition()->getDefaultTransformMapping();
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setAffordanceChecker($callback)
    {
        $this->affordanceChecker = $callback;

        return $this;
    }

    public function setControllerName($name)
    {
        $this->controllerName = $name;

        return $this;
    }

    public function setAcceptedContentTypes(array $types)
    {
        $this->acceptedContentTypes = $types;

        return $this;
    }

    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Should the Id be appended to the Uri?
     *
     * @param bool $value
     * @return $this
     */
    public function setShouldAppendId($value)
    {
        $this->shouldAppendId = $value;

        return $this;
    }

    /**
     * @param \Rested\Definition\TransformMapping $transformMapping
     * @return $this
     */
    public function setTransformMapping(TransformMapping $transformMapping = null)
    {
        $this->transformMapping = $transformMapping;

        return $this;
    }

    public function setSummary($value)
    {
        $this->summary = $value;

        return $this;
    }

    /**
     * Should the Id be appended to the Uri?
     *
     * @return bool
     */
    public function shouldAppendId()
    {
        return $this->shouldAppendId;
    }

    public static function methodFromType($type)
    {
        switch ($type) {
            case ActionDefinition::TYPE_CREATE:
                return HttpRequest::METHOD_POST;

            case ActionDefinition::TYPE_DELETE:
                return HttpRequest::METHOD_DELETE;

            case ActionDefinition::TYPE_UPDATE:
                return HttpRequest::METHOD_PUT;
        }

        return HttpRequest::METHOD_GET;
    }
}
