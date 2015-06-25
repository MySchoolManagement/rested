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
        $role = array_key_exists('role', $action) ? $action['role'] : null;

        if (($role !== null) && ($this->authorizationChecker->isGranted($role) === false)) {
            abort(401);
        }

        return $next($request);
    }
}