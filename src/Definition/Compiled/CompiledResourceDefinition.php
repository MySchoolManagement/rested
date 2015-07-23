<?php
namespace Rested\Definition\Compiled;

use Rested\Definition\ActionDefinition;
use Rested\Definition\ResourceDefinition;
use Rested\FactoryInterface;
use Rested\Transforms\CompiledTransformMappingInterface;
use Rested\Transforms\TransformInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompiledResourceDefinition extends ResourceDefinition implements CompiledResourceDefinitionInterface
{

    /**
     * @var bool
     */
    protected $accessControlApplied = false;

    public function __construct(
        FactoryInterface $factory,
        $path, $name, $summary, $description,
        array $actions = [],
        TransformInterface $defaultTransform,
        CompiledTransformMappingInterface $defaultTransformMapping)
    {
        $this->actions = $actions;
        $this->defaultTransform = $defaultTransform;
        $this->defaultTransformMapping = $defaultTransformMapping;
        $this->description = $description;
        $this->factory = $factory;
        $this->name = $name;
        $this->path = $path;
        $this->summary = $summary;

        foreach ($actions as $action) {
            $action->setResourceDefinition($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyAccessControl(AuthorizationCheckerInterface $authorizationChecker)
    {
        if ($this->accessControlApplied === false) {
            $this->actions = array_filter(
                $this->actions,
                function ($value) use ($authorizationChecker) {
                    if ($authorizationChecker->isGranted(ActionDefinition::SECURITY_ATTRIBUTE, $value) === true) {
                        $value->applyAccessControl($authorizationChecker);
                        return true;
                    } else {
                        return false;
                    }
                }
            );

            $this->accessControlApplied = true;
            $this->defaultTransformMapping->applyAccessControl($authorizationChecker);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findActionByRouteName($routeName)
    {
        foreach ($this->actions as $action) {
            if ((is_a($action, CompiledActionDefinitionInterface::class) === true)
                && ($action->getRouteName() === $routeName)) {
                return $action;
            }
        }

        return null;
    }
}
