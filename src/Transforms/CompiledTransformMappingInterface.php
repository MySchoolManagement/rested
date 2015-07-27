<?php
namespace Rested\Transforms;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface CompiledTransformMappingInterface extends TransformMappingInterface
{

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @return void
     */
    public function applyAccessControl(AuthorizationCheckerInterface $authorizationChecker);
}
