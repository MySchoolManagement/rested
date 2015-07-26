<?php
namespace Rested\Definition;

use Rested\Transforms\TransformInterface;
use Rested\Transforms\TransformMappingInterface;
use Symfony\Component\HttpFoundation\Request as Request;

class ActionDefinition implements ActionDefinitionInterface
{

    const SECURITY_ATTRIBUTE = 'rested_action';

    const TYPE_COLLECTION = 'collection';
    const TYPE_CREATE = 'create';
    const TYPE_DELETE = 'delete';
    const TYPE_INSTANCE = 'instance';
    const TYPE_INSTANCE_AFFORDANCE = 'instance_affordance';
    const TYPE_UPDATE = 'update';

    /**
     * @var callable
     */
    protected $affordanceAvailabilityCallback;

    /**
     * @var string[]
     */
    protected $acceptedContentType = 'application/json';

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var \Rested\Transforms\TransformInterface
     */
    protected $defaultTransform;

    /**
     * @var \Rested\Transforms\TransformMappingInterface
     */
    protected $defaultTransformMapping;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var \Rested\Definition\ResourceDefinitionInterface
     */
    protected $resourceDefinition;

    /**
     * @var bool
     */
    protected $shouldAppendId;

    /**
     * @var string
     */
    protected $summary;

    /**
     * @var \Rested\Definition\Parameter[]
     */
    protected $tokens = [];

    /**
     * @var null|\Rested\Transforms\TransformInterface
     */
    protected $transform;

    /**
     * @var null|\Rested\Transforms\TransformMappingInterface
     */
    protected $transformMapping;

    /**
     * @var string
     */
    protected $type;

    /**
     * Constructs a new ActionDefinition.
     *
     * @param \Rested\Definition\ResourceDefinitionInterface $resourceDefinition The resource definition that owns the action.
     * @param \Rested\Transforms\TransformInterface $defaultTransform Default transform to use when converting a mapping.
     * @param \Rested\Transforms\TransformMappingInterface $defaultTransformMapping Default transform mapping to use in the absence of an override.
     * @param string $type Type of action to add on this resource.
     * @param string $id Name of the action. This is used to construct role names, Url's, etc.
     */
    public function __construct(
        TransformInterface $defaultTransform,
        TransformMappingInterface $defaultTransformMapping,
        $type,
        $id)
    {
        // convert hyphenated to camelcase
        $this->controllerName = preg_replace_callback(
            '!\-[a-zA-Z]!',
            function ($matches) {
                return strtoupper(str_replace('-', '', $matches[0]));
            },
            $id
        );

        $this->id = $id;
        $this->defaultTransform = $defaultTransform;
        $this->defaultTransformMapping = $defaultTransformMapping;
        $this->type = $type;
        $this->httpMethod = self::httpMethodFromType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function addToken($name, $type, $defaultValue = null, $description = null)
    {
        // FIXME: should come from a factory
        $this->tokens[] = new Parameter($name, $type, $defaultValue, $description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAcceptedContentType()
    {
        return $this->acceptedContentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffordanceAvailabilityCallback()
    {
        return $this->affordanceAvailabilityCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransform()
    {
        return $this->transform ?: $this->defaultTransform;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformMapping()
    {
        return $this->transformMapping ?: $this->defaultTransformMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function isAffordanceAvailable($instance = null)
    {
        if ($this->affordanceAvailabilityCallback === null) {
            return true;
        }

        return call_user_func_array($this->affordanceAvailabilityCallback, [$instance]);
    }

    /**
     * {@inheritdoc}
     */
    public function setAcceptedContentType($mimeType)
    {
        $this->acceptedContentType = $mimeType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAffordanceAvailabilityCallback($callback)
    {
        $this->affordanceAvailabilityCallback = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerName($name)
    {
        $this->controllerName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        $this->description = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHttpMethod($method)
    {
        $this->httpMethod = $method;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldAppendId($value)
    {
        $this->shouldAppendId = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTransform(TransformInterface $transform)
    {
        $this->transform = $transform;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTransformMapping(TransformMappingInterface $transformMapping)
    {
        $this->transformMapping = $transformMapping;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSummary($value)
    {
        $this->summary = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldAppendId()
    {
        return $this->shouldAppendId;
    }

    public static function httpMethodFromType($type)
    {
        switch ($type) {
            case ActionDefinition::TYPE_CREATE:
                return Request::METHOD_POST;

            case ActionDefinition::TYPE_DELETE:
                return Request::METHOD_DELETE;

            case ActionDefinition::TYPE_UPDATE:
                return Request::METHOD_PUT;
        }

        return Request::METHOD_GET;
    }
}
