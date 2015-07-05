<?php
namespace Rested\Http\Middleware;

use Closure;
use Rested\Security\AccessVoter;
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
        if ($this->authorizationChecker->isGranted(AccessVoter::ATTRIB_ACTION_ACCESS, $this) === false) {
            abort(401);
        }

        return $next($request);
    }
}