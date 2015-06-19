<?php
namespace Rested;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use Illuminate\Routing\Router;
use Rested\Definition\ActionDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractResource extends Controller
{

    private $context;

    private $definition;

    private $exportMapping;

    public function __construct(Router $router = null)
    {
        // if we're in the request scope then create a context
        if (($router !== null) && (($route = $router->getCurrentRoute()) !== null)) {
            $this->context = new RequestContext($router->getCurrentRequest(), $this);
            $this->urlService = app('url');
        }
    }

    public abstract function createDefinition();

    /**
     * Aborts the request and sends the given message and HTTP status code
     * to the client.
     *
     * @param string $message Message to pass to the client.
     * @param integer $statusCode HTTP status code.
     *
     * @return Response
     */
    public function abort($message, $statusCode)
    {
        throw new \Exception("NOT IMPLEMENTED");
        switch ($statusCode) {
            case 400: throw new BadRequestHttpException($message);
            case 401: throw new UnauthorizedHttpException(rand(), $message);
            case 404: throw new NotFoundHttpException($message);
            case 409: throw new ConflictHttpException($message);
        }

        throw new \Exception('Unknown status code, perhaps this needs to be added to abort()');
    }

    public function allowPrettyInterface()
    {
        return true;
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
        if ($applyLimits == true) {
            if (is_subclass_of($queryBuilder, 'ModelCriteria') == true) {
                $queryBuilder
                    ->setLimit($this->getLimit())
                    ->setOffset($this->getOffset());
            }
        }

        $mapping = $this->getExportMapping();

        foreach ($mapping->getFilters() as $filter) {
            // @todo: security
            /*if ($app['security']->isGranted($filter->getRequiredPermission()) == false) {
                continue;
            }*/

            if (($value = $this->getFilter($filter->getName())) !== null) {
                if ($value == 'null') {
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

    public function done(Result $result, $statusCode = 200, $headers = [])
    {
        return new JsonResponse($result, $statusCode, $headers);
    }


    public function export($instance)
    {
        $instanceDef = $this->getDefinition()->getInstanceDefinition();

        if ($instanceDef === null) {
            return null;
        }

        return $instanceDef->export($instance);
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getFilter($name)
    {
        return $this
                ->getContext()
                ->getFilter($name)
            ;
    }

    public function getLocale()
    {
        return $this
                ->getContext()
                ->getRequest()
                ->getLocale()
            ;
    }

    public function setFilter($name, $value)
    {
        return $this
                ->getContext()
                ->setFilter($name, $value)
            ;
    }

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public final function getDefinition()
    {
        if ($this->definition !== null) {
            return $this->definition;
        }

        return ($this->definition = $this->createDefinition());
    }

    public function getLimit()
    {
        return $this
                ->getContext()
                ->getLimit()
            ;
    }

    /***
     * Gets the mapping model used specifically for create operations.
     */
    public function getCreateModel()
    {
        return $this->getModel();
    }

    /***
     * Gets the mapping model used specifically for update operations.
     */
    public function getUpdateModel()
    {
        return $this->getModel();
    }

    /**
     * Gets the mapping model to use for update/create/delete operations.
     *
     * @return Mapping
     */
    public function getModel()
    {
        return $this->getExportMapping();
    }

    public function getOffset()
    {
        return $this
                ->getContext()
                ->getOffset()
            ;
    }

    public function getParameter($key)
    {
        return $this
                ->getContext()
                ->getParameter($key)
            ;
    }

    public function createInstanceHref($instance)
    {
        $action = $this->getDefinition()->findAction(ActionDefinition::TYPE_INSTANCE);

        if ($action === null) {
            return null;
        }

        return $this->urlService->route($action->getRouteName(), ['id' => 1]);
    }

    /**
     * Gets the current user.
     *
     * @return Symfony\Component\Security\Core\User\AdvancedUserInterface
     */
    public function getUser()
    {
        throw new \Exception("Not implemented");
        return $this->getService('security.context')->getUser();
    }

    public function wantsExpansion($name)
    {
        return $this
                ->getContext()
                ->wantsExpansion($name)
            ;
    }

    public function wantsField($name)
    {
        return $this
                ->getContext()
                ->wantsField($name)
            ;
    }
}
