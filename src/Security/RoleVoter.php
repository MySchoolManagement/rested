<?php
namespace Rested\Security;

use Rested\Definition\ActionDefinition;
use Rested\Definition\Filter;
use Rested\Definition\GetterField;
use Rested\Definition\SetterField;
use Rested\NameGenerator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class RoleVoter implements VoterInterface
{

    const INTERFACE_COMPILED_ACTION = 'Rested\Definition\Compiled\CompiledActionDefinitionInterface';
    const INTERFACE_COMPILED_FIELD = 'Rested\Definition\Compiled\CompiledFieldInterface';
    const INTERFACE_COMPILED_FILTER = 'Rested\Definition\Compiled\CompiledFilterInterface';

    /**
     * @var \Rested\NameGenerator
     */
    protected $nameGenerator;

    /**
     * @var \Symfony\Component\Security\Core\Role\RoleHierarchyInterface
     */
    protected $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy, NameGenerator $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return [
            ActionDefinition::SECURITY_ATTRIBUTE,
            Filter::SECURITY_ATTRIBUTE,
            GetterField::SECURITY_ATTRIBUTE,
            SetterField::SECURITY_ATTRIBUTE,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return [
            self::INTERFACE_COMPILED_ACTION,
            self::INTERFACE_COMPILED_FIELD,
            self::INTERFACE_COMPILED_FILTER,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, $this->getSupportedAttributes());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        foreach ($this->getSupportedClasses() as $supportedClass) {
            if (($supportedClass === $class) || (is_subclass_of($class, $supportedClass) === true)) {
                return true;
            }
        }

        return false;
    }

    protected function attributeSupportByObject($attribute, $object)
    {
        if ($attribute === ActionDefinition::SECURITY_ATTRIBUTE) {
            if (is_a($object, self::INTERFACE_COMPILED_ACTION) === false) {
                return false;
            }
        } else if ($attribute === Filter::SECURITY_ATTRIBUTE) {
            if (is_a($object, self::INTERFACE_COMPILED_FILTER) === false) {
                return false;
            }
        } else {
            if (is_a($object, self::INTERFACE_COMPILED_FIELD) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (($object === null) || ($this->supportsClass(get_class($object)) === false)) {
            return self::ACCESS_ABSTAIN;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;
        $roles = $this->extractRoles($token);

        foreach ($attributes as $attribute) {
            if ($this->attributeSupportByObject($attribute, $object) === false) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;
            $acceptedRoles = $object->getRoles($attribute);

            foreach ($roles as $role) {
                foreach ($acceptedRoles as $acceptedRole) {
                    if ($role->getRole() === $acceptedRole->getRole()) {
                        return self::ACCESS_GRANTED;
                    }
                }
            }
        }

        return $vote;
    }

    protected function extractRoles(TokenInterface $token)
    {
        // FIXME: this is the definition of a hack
        if (property_exists($token, 'reachableRoles') === true) {
            return $token->reachableRoles;
        }

        if ($token->getUsername() === 'anon.') {
            $roles = [
                new Role('ROLE_PUBLIC'),
            ];
        } else {
            $roles = $token->getRoles();
        }

        $token->reachableRoles = $this->roleHierarchy->getReachableRoles($roles);

        return $token->reachableRoles;
    }
}
