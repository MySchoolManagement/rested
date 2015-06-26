<?php
namespace Rested;

use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Provides extra helpers for dealing with endpoints that create content.
 */
abstract class EloquentResource extends AbstractResource
{

    /**
     * @var \Illuminate\Database\DatabaseManager;
     */
    protected $databaseManager;

    public function __construct(Router $router = null, DatabaseManager $databaseManager = null)
    {
        parent::__construct($router);

        $this->databaseManager = $databaseManager;
    }

    /**
     * Applies filters from mapping data to the given query builder.
     *
     * @param \Illuminate\Database\Model $queryBuilder
     * @param bool $applyLimits Apply offset/limit?
     *
     * @return \Illuminate\Database\Model
     */
    public function applyFilters($queryBuilder, $applyLimits = true)
    {
        if ($applyLimits == true) {

            $queryBuilder = $queryBuilder
                ->take($this->getLimit())
                ->offset($this->getOffset())
            ;
        }

        /*$mapping = $this->getExportMapping();

        foreach ($mapping->getFilters() as $filter) {
            // @todo: security
            /*if ($app['security']->isGranted($filter->getRequiredPermission()) == false) {
                continue;
            }*//*

            if (($value = $this->getFilter($filter->getName())) !== null) {
                if ($value == 'null') {
                    $value = null;
                }

                $callable = $filter->getCallable();

                if ($callable !== null) {
                    $queryBuilder->$callable($value);
                }
            }
        }*/

        return $queryBuilder;
    }

    public function collection(Request $request)
    {
        $items = [];

        // build data
        $builder = $this->createQueryBuilder(true);

        foreach ($builder->get() as $item) {
            $items[] = $this->export($item);
        }

        // build total
        $total = $this->createQueryBuilder(true, false)->count();
        $response = Response::createCollection($items, $total);

        return $this->done($response);
    }

    public function create(Request $request)
    {
        $data = $request->json()->all();

        // check for a duplicate record
        if ($this->hasDuplicate($request, $data) == true) {
            return $this->abort(HttpResponse::HTTP_CONFLICT, ['An item with this name already exists']);
        }

        $instance = null;

        $closure = function() use ($data, &$instance) {
            $instance = $this->createInstance($data);

            if ($instance !== null) {
                $this->onCreated($instance);
            }
        };

        if ($this->useTransaction() === true) {
            $this->databaseManager->transaction($closure);
        } else {
            $closure();
        }

        $item = $this->exportAll($instance);
        $response = Response::createInstance($item);

        return $this->done($response, HttpResponse::HTTP_CREATED);
    }


    /**
     * Creates a new instance of the type stored in the model.
     *
     * @param Form $form
     *
     * @return object|null
     */
    protected function createInstance(array $data)
    {
        $instance = $this->getCurrentModel()->apply('en', $data);
        $instance->save();

        return $instance;
    }

    /**
     * Creates a query builder from the bound model class.
     *
     * Optionally, we apply filters and limits.
     *
     * @param bool $applyFilters
     * @param bool $applyLimits
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createQueryBuilder($applyFilters = false, $applyLimits = true)
    {
        return $this->createQueryBuilderFor($this->getCurrentModel()->getDefiningClass(), $applyFilters, $applyLimits);
    }

    protected function createQueryBuilderFor($class, $applyFilters = false, $applyLimits = true)
    {
        $queryBuilder = new $class();

        // apply current locale (not all models handled by this class have i18n enabled)
        /*if (method_exists($queryBuilder, 'joinWithI18n') == true) {
            $queryBuilder->joinWithI18n($this->getRequest()->getLocale());
        }*/

        if ($applyFilters == true) {
            $queryBuilder = $this->applyFilters($queryBuilder, $applyLimits);
        }

        return $queryBuilder;
    }

    public function instance(Request $request, $id = null)
    {
        $instance = $this->findInstance($id);

        if ($instance === null) {
            $this->abort(404);
        }

        $item = $this->export($instance);
        $response = Response::createInstance($item);

        return $this->done($response);
    }

    public function handleUpdate(Request $request, $id)
    {
        // TODO: permissions

        $form = $this->createForm($this->getContentFormType($this->getUpdateModel()), null, ['method' => 'PUT']);
        $form->handleRequest($request);

        $result = new Result();
        $result->addForm($form);
        $statusCode = 200;

        if (($instance = $this->findInstance($id)) === null) {
            $this->abort(404);
        } else {
            $result->data = $instance->exportAll($this->getContext());

            if ($form->isValid() == true) {
                $con = \Propel::getConnection();
                $con->beginTransaction();
                {
                    $this->updateInstance($instance, $form);
                    $this->onUpdated($result, $form, $instance);
                }
                $con->commit();

                $result->data = $instance->exportAll($this->getContext());
            } else {
                $statusCode = 400;
            }
        }

        return $this->done($result, $statusCode);
    }

    /**
     * Find an instance from an ID.
     *
     * @param  mixed $id ID of the resource.
     *
     * @return mixed|null Content for the given ID or null.
     */
    protected function findInstance($id)
    {
        return $this->createQueryBuilder()->findOneByPrimaryKey($id);
    }

    /**
     * With the given data, check to see if an existing item already exists.
     *
     * @param Request $request
     * @param array $data
     *
     * @return bool
     */
    protected function hasDuplicate(Request $request, array $data)
    {
        return false;
    }

    /**
     * Called when a new instance of the content type has been created.
     *
     * If you make changes to the model of the instance then you must call
     * save.
     *
     * @param object $instance Instance that was created.
     */
    protected function onCreated($instance)
    {

    }

    /**
     * Called when an instance of the content type has been updated.
     *
     * If you make changes to the model of the instance then you must call
     * save.
     *
     * @param object $instance Instance that was updated.
     */
    protected function onUpdated($instance)
    {

    }

    /**
     * Updates an existing instance of the content type.
     *
     * @param object $instance
     * @param array $data
     *
     * @return object|null
     */
    protected function updateInstance($instance, array $data)
    {
        $this
            ->getCurrentModel()
            ->apply($this->getRequest()->getLocale(), $data, $instance)
            ->save();

        return $instance;
    }

    /**
     * @return bool
     */
    public function useTransaction()
    {
        return true;
    }
}