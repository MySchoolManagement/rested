<?php
namespace Rested\Compiler;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompilerCache implements CompilerCacheInterface
{

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var CompiledResourceDefinitionInterface[]
     */
    public $resourceDefinitions = [];

    /**
     * @var CompiledResourceDefinitionInterface[]
     */
    public $resourceDefinitionsByModel = [];

    /**
     * @param \Rested\Definition\Compiled\CompiledResourceDefinitionInterface|null $resourceDefinition
     * @return void
     */
    protected function applyAccessControl(CompiledResourceDefinitionInterface $resourceDefinition = null)
    {
        if (($this->authorizationChecker !== null) && ($resourceDefinition !== null)) {
            $resourceDefinition->applyAccessControl($this->authorizationChecker);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findResourceDefinition($routeName)
    {
        $resourceDefinition = null;

        if (array_key_exists($routeName, $this->resourceDefinitions) === true) {
            $resourceDefinition = $this->resourceDefinitions[$routeName];
        }

        $this->applyAccessControl($resourceDefinition);

        return $resourceDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function findResourceDefinitionForModelClass($modelClass)
    {
        if (array_key_exists($modelClass, $this->resourceDefinitionsByModel) === true) {
            $resourceDefinition = $this->resourceDefinitionsByModel[$modelClass];

            $this->applyAccessControl($resourceDefinition);

            return $resourceDefinition;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function registerResourceDefinition(
        $routeName,
        CompiledResourceDefinitionInterface $resourceDefinition)
    {
        $this->resourceDefinitions[$routeName] = $resourceDefinition;

        foreach ($resourceDefinition->getActions() as $action) {
            $this->resourceDefinitionsByModel[$action->getTransformMapping()->getModelClass()] = $resourceDefinition;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return CompilerHelper::serialize(get_object_vars($this), ['authorizationChecker']);
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
}
