<?php
namespace Rested\Security;

use App\User;
use Rested\Exceptions\UnsupportedAttributeException;
use Rested\Helper;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

class AccessVoter extends AbstractVoter
{

    const ATTRIB_ACTION_ACCESS = 'ACTION_ACCESS';
    const ATTRIB_FIELD_GET = 'GET';
    const ATTRIB_FIELD_SET = 'SET';
    const ATTRIB_FILTER = 'FILTER';

    const CLASS_ACTION = 'Rested\Definition\ActionDefinition';
    const CLASS_FIELD = 'Rested\Definition\Field';
    const CLASS_FILTER = 'Rested\Definition\Filter';

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
    protected function isGranted($attribute, $object, $user = null)
    {
        $class = get_class($object);

        if (($user instanceof User) === false) {
            return false;
        }

        if ($attribute === self::ATTRIB_ACTION_ACCESS) {
            if ($class !== self::CLASS_ACTION) {
                throw new UnsupportedAttributeException($attribute, $class);
            }
        } else if ($attribute === self::ATTRIB_FILTER) {
            if ($class !== self::CLASS_FILTER) {
                throw new UnsupportedAttributeException($attribute, $class);
            }
        } else {
            if ($class !== self::CLASS_FIELD) {
                throw new UnsupportedAttributeException($attribute, $class);
            }
        }

        $roles = Helper::createRolesForObject($attribute, $object);

        return sizeof($roles) ? $user->isGranted($roles) : false;
    }
}