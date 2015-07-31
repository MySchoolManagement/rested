<?php
namespace Rested\Definition\Compiled;


use Rested\Definition\ActionDefinition;
use Rested\Transforms\TransformInterface;
use Rested\Transforms\TransformMappingInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompiledActionDefinition extends ActionDefinition implements CompiledActionDefinitionInterface
{

    /**
     * @var bool
     */
    protected $accessControlApplied = false;

    /**
     * @var string
     */
    protected $endpointUrl;

    /**
     * @var \Symfony\Component\Security\Core\Role\RoleInterface
     */
    protected $roles;

    /**
     * @var string
     */
    protected $routeName;

    public function __construct($routeName, array $roles, $endpointUrl, $type, $id, $shouldAppendId, $summary, $description, $controllerName, $httpMethod, $acceptedContentType, $affordanceAvailabilityCallback, array $tokens, TransformInterface $transform, TransformMappingInterface $transformMapping)
    {
        $this->acceptedContentType = $acceptedContentType;
        $this->affordanceAvailabilityCallback = $affordanceAvailabilityCallback;
        $this->controllerName = $controllerName;
        $this->description = $description;
        $this->endpointUrl = $endpointUrl;
        $this->id = $id;
        $this->httpMethod = $httpMethod;
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
    public function applyAccessControl(AuthorizationCheckerInterface $authorizationChecker)
    {
        if ($this->accessControlApplied === false) {
            $this->accessControlApplied = true;
            $this->transformMapping->applyAccessControl($authorizationChecker);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
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
