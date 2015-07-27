<?php
namespace Rested;

use App\Rested\Resources\StudentsResource;
use Illuminate\Support\Facades\Auth;
use Rested\Security\AccessVoter;
use Rested\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Nocarrier\Hal;
use Rested\Definition\ActionDefinition;
use Rested\Http\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

trait RestedResource
{

    protected $authorizationChecker;

    private $cacheDefinition;

    protected $urlGenerator;

    public function handle()
    {
        $request = $this->getCurrentRequest();
        $controller = $request->get('_rested_controller');
        $action = $this->getCurrentAction();

        if ($this->authorizationChecker !== null) {
            if ($this->authorizationChecker->isGranted(AccessVoter::ATTRIB_ACTION_ACCESS, $action) === false) {
                $this->abort(HttpResponse::HTTP_UNAUTHORIZED);
            }
        }

        if (in_array($request->getMethod(), ['PATCH', 'POST', 'PUT']) === true) {
            $this->validate($request);
        }

        return call_user_func_array([$this, $controller], func_get_args());
    }

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public abstract function createDefinition();

    //protected abstract function extractDataFromRequest(HttpRequest $request);

    public function getAuthorizationChecker()
    {
        return $this->authorizationChecker;
    }

    /**
     * @return \Rested\FactoryInterface
     */
    public abstract function getFactory();

    /**
     * Aborts the request and sends the given message and HTTP status code
     * to the client.
     *
     * @param string $message Message to pass to the client.
     * @param integer $statusCode HTTP status code.
     */
    public function abort($statusCode, $attributes = [])
    {
        $response = new Hal($this->getCurrentActionUri());
        $response->setData($attributes);

        switch ($statusCode) {
            case 401:
                throw new UnauthorizedHttpException('Unauthorized');
            case 404:
                throw new NotFoundHttpException();
            case 409:
                throw new ConflictHttpException($response->asJson());

            default:
                throw new HttpException($statusCode, $response->asJson(), null, ['content-type' => 'application/json']);
        }
    }

    /**
     * Applies filters from mapping data to the given query builder.
     *
     * @param QueryBuilder|Criteria $queryBuilder
     * @param boolean $applyLimits Apply offset/limit?
     *
     * @return QueryBuilder|Criteria
     */
    public function applyFilters($queryBuilder, $applyLimits = true)
    {
        $context = $this->getCurrentContext();

        if ($applyLimits == true) {
            if (is_subclass_of($queryBuilder, 'ModelCriteria') == true) {
                $queryBuilder
                    ->setLimit($context->getLimit())
                    ->setOffset($context->getOffset());
            }
        }

        $mapping = $this->getExportMapping();

        foreach ($mapping->getFilters() as $filter) {
            // @todo: security
            /*if ($app['security']->isGranted($filter->getRequiredPermission()) == false) {
                continue;
            }*/

            if (($value = $context->getFilterValue($filter->getName())) !== null) {
                if ($value === 'null') {
                    $value = null;
                }

                $callable = $filter->getCallable();

                if ($callable !== null) {
                    $queryBuilder->$callable($value);
                }
            }
        }

        return $queryBuilder;
    }

    public function done(Hal $response = null, $statusCode = HttpResponse::HTTP_OK, $headers = [])
    {
        $headers = array_merge(['content-type' => 'application/json'], $headers);
        $json = $response ? $response->asJson($this) : '';

        return new HttpResponse($json, $statusCode, $headers);
    }


    public function export($instance, $all = false)
    {
        if (($model = $this->getDefinition()->getModel()) !== null) {
            return $all ? $model->exportAll($instance) : $model->export($instance);
        }

        return null;
    }

    public function exportAll($instance)
    {
        return $this->export($instance, true);
    }

    /**
     * @return \Rested\RequestContext
     */
    public function getCurrentContext()
    {
        return $this->getFactory()->resolveContextForRequest($this->getCurrentRequest(), $this);
    }

    public function getCurrentAction()
    {
        if (($action = $this->getDefinition()->findActionByRouteName($this->getCurrentContext()->getRouteName())) !== null) {
            return $action;
        }

        return null;
    }

    public function getCurrentActionType()
    {
        return $this->getCurrentContext()->getActionType();
    }

    public function getCurrentActionUri()
    {
        if (($action = $this->getCurrentAction()) !== null) {
            return $this->urlGenerator->generate($action->getRouteName());
        }

        return null;
    }

    public function getCurrentModel()
    {
        $routeName = $this->getCurrentContext()->getRouteName();

        if (($action = $this->getDefinition()->findActionByRouteName($routeName)) !== null) {
            return $action->getModel();
        }

        return null;
    }

    public function getLocale()
    {
        return $this
            ->getCurrentRequest()
            ->getLocale()
            ;
    }

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public final function getDefinition()
    {
        if ($this->cacheDefinition !== null) {
            return $this->cacheDefinition;
        }

        return ($this->cacheDefinition = $this->createDefinition());
    }

    public function createInstanceHref($instance)
    {
        if (($action = $this->getDefinition()->findAction(ActionDefinition::TYPE_INSTANCE)) !== null) {
            return $this->urlGenerator->generate($action->getRouteName(), ['id' => $action->getModel()->getPrimaryKeyValueForInstance($instance)]);
        }

        return null;
    }

    /**
     * Gets the current user.
     *
     * @return \App\User|null
     */
    public abstract function getUser();

    public function validate(Request $request)
    {
        $model = $this->getCurrentModel();
        $messages = $model->validate($this->extractDataFromRequest($request));

        if (sizeof($messages) > 0) {
            $this->abort(HttpResponse::HTTP_UNPROCESSABLE_ENTITY, [
                'validation_messages' => $messages,
            ]);
        }
    }
}
