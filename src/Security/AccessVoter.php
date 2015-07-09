<?php
namespace Rested\Security;

use Rested\Helper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AccessVoter implements VoterInterface
{

    const ATTRIB_ACTION_ACCESS = 'ACTION_ACCESS';
    const ATTRIB_FIELD_GET = 'GET';
    const ATTRIB_FIELD_SET = 'SET';
    const ATTRIB_FILTER = 'FILTER';

    const CLASS_ACTION = 'Rested\Definition\ActionDefinition';
    const CLASS_FIELD = 'Rested\Definition\Field';
    const CLASS_FILTER = 'Rested\Definition\Filter';

    protected $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return [
            self::ATTRIB_ACTION_ACCESS,
            self::ATTRIB_FIELD_GET,
            self::ATTRIB_FIELD_SET,
            self::ATTRIB_FILTER,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return [
            self::CLASS_ACTION,
            self::CLASS_FIELD,
            self::CLASS_FILTER,
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
        $class = get_class($object);

        if ($attribute === self::ATTRIB_ACTION_ACCESS) {
            if ($class !== self::CLASS_ACTION) {
                return false;
            }
        } else if ($attribute === self::ATTRIB_FILTER) {
            if ($class !== self::CLASS_FILTER) {
                return false;
            }
        } else {
            if ($class !== self::CLASS_FIELD) {
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
        $roles = $this->roleHierarchy->getReachableRoles($this->extractRoles($token));

        foreach ($attributes as $attribute) {
            if ($this->attributeSupportByObject($attribute, $object) === false) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;
            $acceptedRoles = Helper::createRolesForObject($attribute, $object);

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
        if ($token->getUsername() === 'anon.') {
            return [
                new Role('ROLE_PUBLIC'),
            ];
        }

        return $token->getRoles();
    }
}
