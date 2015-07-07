<?php
namespace Rested;

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
use Illuminate\Support\Facades\Validator;

trait RestedResource
{

    private $authorizationChecker;

    private $cacheDefinition;

    private $context;

    private $currentActionType;

    private $urlGenerator;

    public function initRestedResource(UrlGeneratorInterface $urlGenerator = null,
        AuthorizationCheckerInterface $authorizationChecker = null, Request $request = null,$currentActionType = null)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->currentActionType = $currentActionType;
        $this->urlGenerator = $urlGenerator;

        // if we're in the request scope then create a context
        if (($authorizationChecker !== null) && ($request !== null)) {
            $this->context = new RequestContext($request, $this);

            if ($authorizationChecker->isGranted(AccessVoter::ATTRIB_ACTION_ACCESS, $this->getCurrentAction()) === false) {
                $this->abort(HttpResponse::HTTP_UNAUTHORIZED);
            }

            if (in_array($request->getMethod(), ['PATCH', 'POST', 'PUT']) === true) {
                $this->validate();
            }
        }
    }

    /**
     * @return Rested\Definition\ResourceDefinition
     */
    public abstract function createDefinition();

    protected function getAuthorizationChecker()
    {
        return $this->authorizationChecker;
    }

    /**
     * @return Rested\FactoryInterface
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

    public function done(Response $response = null, $statusCode = HttpResponse::HTTP_OK, $headers = [])
    {
        $headers = array_merge(['content-type' => 'application/json'], $headers);
        $json = $response ? $response->asJson($this) : '';

        return new HttpResponse($json, $statusCode, $headers);
    }


    public function export($instance)
    {
        if (($model = $this->getDefinition()->getModel()) !== null) {
            return $model->export($instance);
        }

        return null;
    }

    public function exportAll($instance)
    {
        if (($model = $this->getDefinition()->getModel()) !== null) {
            return $model->exportAll($instance);
        }

        return null;
    }

    /**
     * @return \Rested\RequestContext
     */
    public function getContext()
    {
        return $this->context;
    }

    public function getCurrentAction()
    {
        if (($action = $this->getDefinition()->findAction($this->currentActionType)) !== null) {
            return $action;
        }

        return null;
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
        if (($action = $this->getDefinition()->findAction($this->currentActionType)) !== null) {
            return $action->getModel();
        }

        return null;
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
        if ($this->cacheDefinition !== null) {
            return $this->cacheDefinition;
        }

        return ($this->cacheDefinition = $this->createDefinition());
    }

    public function getLimit()
    {
        return $this
            ->getContext()
            ->getLimit()
            ;
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
    public function getUser()
    {
        return $this->getContext()->getRequest()->user();
    }

    public function validate()
    {
        $model = $this->getCurrentModel();

        if ($model !== null) {
            $request = $this->getContext()->getRequest();
            $rules = [];

            foreach ($model->getFields() as $field) {
                if ($field->isModel() === true) {
                    $parameters = $field->getValidationParameters();

                    // add a validator for the data type of this field
                    $parameters .= '|' . $field->getTypeValidatorName();

                    $rules[$field->getName()] = $parameters;
                }
            }

            $validator = Validator::make($request->json()->all(), $rules);

            if ($validator->fails() === true) {
                $failed = $validator->failed();
                $messages = $validator->messages();;
                $responseMessages = [];

                foreach ($failed as $field => $rules) {
                    $responseMessages[$field] = [];

                    foreach ($rules as $rule => $parameters) {
                        $responseMessages[$field][$rule] = $messages->first($field);
                    }
                }

                $this->abort(422, [
                    'validation_messages' => $responseMessages
                ]);
            }
        }
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