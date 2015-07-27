<?php
namespace Rested\Definition\Compiled;


use Rested\Definition\ActionDefinition;
use Rested\Transforms\TransformInterface;
use Rested\Transforms\TransformMappingInterface;

class CompiledActionDefinition extends ActionDefinition implements CompiledActionDefinitionInterface
{

    /**
     * @var string
     */
    protected $absoluteEndpointUrl;

    /**
     * @var string
     */
    protected $relativeEndpointUrl;

    /**
     * @var \Symfony\Component\Security\Core\Role\RoleInterface
     */
    protected $roles;

    /**
     * @var string
     */
    protected $routeName;

    public function __construct($routeName, array $roles, $absoluteEndpointUrl, $relativeEndpointUrl, $type, $id, $shouldAppendId, $summary, $description, $controllerName, $httpMethod, $acceptedContentType, $affordanceAvailabilityCallback, array $tokens, TransformInterface $transform, TransformMappingInterface $transformMapping)
    {
        $this->absoluteEndpointUrl = $absoluteEndpointUrl;
        $this->acceptedContentType = $acceptedContentType;
        $this->affordanceAvailabilityCallback = $affordanceAvailabilityCallback;
        $this->controllerName = $controllerName;
        $this->description = $description;
        $this->id = $id;
        $this->httpMethod = $httpMethod;
        $this->relativeEndpointUrl = $relativeEndpointUrl;
        $this->roles = $roles;
        $this->routeName = $routeName;
        $this->shouldAppendId = $shouldAppendId;
        $this->summary = $summary;
        $this->transform = $transform;
        $this->transformMapping = $transformMapping;
        $this->type = $type;
        $this->tokens = $tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpointUrl($absolute = true)
    {
        return $absolute ? $this->absoluteEndpointUrl : $this->relativeEndpointUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceDefinition(CompiledResourceDefinitionInterface $resourceDefinition)
    {
        $this->resourceDefinition = $resourceDefinition;

        return $this;
    }
}
