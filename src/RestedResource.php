<?php
namespace Rested;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait RestedResource
{

    /**
     * Aborts the request and sends the given message and HTTP status code
     * to the client.
     *
     * @param int $statusCode HTTP status code.
     * @param array Array of attributes to return in the response.
     */
    public function abort($statusCode, array $attributes = [])
    {
        $response = new Hal(null);
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
     * {@inheritdoc}
     */
    public static function createDefinition(FactoryInterface $factory)
    {
        // TODO: implement NotImplementException, irony!
        throw new \Exception();
    }

    public function done(Hal $response = null, $statusCode = HttpResponse::HTTP_OK, $headers = [])
    {
        $headers = array_merge(['content-type' => 'application/json'], $headers);
        $json = $response ? $response->asJson($this) : '';

        return new HttpResponse($json, $statusCode, $headers);
    }

    /**
     * {@inhertidoc}
     */
    public function getCurrentContext()
    {
        return $this
            ->getRestedService()
            ->resolveContextFromRequest($this->getCurrentRequest(), $this->getDefinition())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTransformMapping()
    {
        return $this->getCurrentContext()->getTransformMapping();
    }

    /**
     * @return \Rested\Definition\ResourceDefinition
     */
    public final function getDefinition()
    {
        static $definition = null;

        if ($definition !== null) {
            return $definition;
        }

        return ($definition = static::createDefinition($this->getFactory()));
    }

    public function handle()
    {
        $request = $this->getCurrentRequest();
        $controller = $request->get('_rested')['controller'];
        $action = $this->getCurrentContext()->getAction();

        // for this to happen, the user must not have access to the action
        if ($action === null) {
            $this->abort(HttpResponse::HTTP_UNAUTHORIZED);
        }

        if (in_array($request->getMethod(), [HttpRequest::METHOD_PATCH, HttpRequest::METHOD_POST, HttpRequest::METHOD_PUT]) === true) {
            $this->validate($request);
        }

        return call_user_func_array([$this, $controller], func_get_args());
    }
}
