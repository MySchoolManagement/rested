<?php
namespace Rested\Compiler;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\FactoryInterface;
use Rested\Transforms\DefaultTransform;
use Rested\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CompilerCache implements CompilerCacheInterface
{

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Rested\FactoryInterface
     */
    protected $factory;

    /**
     * @var CompiledResourceDefinitionInterface[]
     */
    protected $resourceDefinitions = [];

    /**
     * @var CompiledResourceDefinitionInterface[]
     */
    protected $resourceDefinitionsByModel = [];

    /**
     * @var \Rested\UrlGeneratorInterface
     */
    protected $urlGenerator;

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
    public function hydrate($data)
    {
        $data = unserialize($data);
        $this->resourceDefinitions = $data['resourceDefinitions'];
        $this->resourceDefinitionsByModel = $data['resourceDefinitionsByModel'];

        foreach (DefaultTransform::$hydrate as $transform) {
            $transform->setServices($this->factory, $this, $this->urlGenerator);
        }
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
        return CompilerHelper::serialize(get_object_vars($this), ['authorizationChecker', 'factory', 'urlGenerator']);
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function setServices(FactoryInterface $factory, UrlGeneratorInterface $urlGenerator)
    {
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
    }
}
