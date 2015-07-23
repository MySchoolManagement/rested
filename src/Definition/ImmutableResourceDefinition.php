<?php
namespace Rested\Definition;

use Rested\Definition\ImmutableTransformMapping;
use Rested\Exceptions\ImmutableException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ImmutableResourceDefinition extends ResourceDefinition
{

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ResourceDefinition $resourceDefinition)
    {
        $name = $resourceDefinition->getName();
        $defaultTransformMapping = $resourceDefinition->getDefaultTransformMapping();

        parent::__construct($name, new ImmutableTransformMapping($authorizationChecker, $defaultTransformMapping));
    }

    /**
     * {@inheritdoc}
     */
    protected function addAction($type, $id)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addAffordance($id = 'instance', $method = HttpRequest::METHOD_POST, $type = Parameter::TYPE_UUID)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addCollection($id = 'collection')
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addCreateAction($id = 'create', TransformMapping $transformMapping = null)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addDeleteAction($id = 'delete', $type = Parameter::TYPE_UUID)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addInstance($id = 'instance', $type = Parameter::TYPE_UUID)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function addUpdateAction($id = 'update', $type = Parameter::TYPE_UUID)
    {
        throw new ImmutableException();
    }


    // TODO: refactor
    public function filterActionsForAccess($instance = null)
    {
        $authChecker = $this->getResource()->getAuthorizationChecker();

        return array_filter(
            $this->actions,
            function ($action) use ($authChecker, $instance) {
                if ($authChecker->isGranted(AccessVoter::ATTRIB_ACTION_ACCESS, $action) === false) {
                    return false;
                }

                if ($action->checkAffordance($instance) === false) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTransformMapping()
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($value)
    {
        throw new ImmutableException();
    }

    /**
     * {@inheritdoc}
     */
    public function setSummary($value)
    {
        throw new ImmutableException();
    }
}
