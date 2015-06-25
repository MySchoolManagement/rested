<?php
namespace Rested\Http\Middleware;

use Closure;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleCheckMiddleware
{

    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function handle($request, Closure $next)
    {
        $action = $request->route()->getAction();
        $roles = array_key_exists('roles', $action) ? $action['roles'] : null;

        if ($roles !== null) {
            $hasAccess = false;

            foreach ($roles as $role) {
                if ($this->authorizationChecker->isGranted($role) === true) {
                    $hasAccess = true;
                    break;
                }
            }

            if ($hasAccess === false) {
                abort(401);
            }
        }

        return $next($request);
    }
}