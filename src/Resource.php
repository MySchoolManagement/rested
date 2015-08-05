<?php
namespace Rested;

use Nocarrier\Hal;
use Rested\Definition\SetterField;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait Resource
{

    /**
     * {@inheritdoc}
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
    public function done(Hal $response = null, $statusCode = HttpResponse::HTTP_OK, $headers = [])
    {
        $headers = array_merge(['content-type' => 'application/json'], $headers);
        $json = $response ? $response->asJson($this) : '';

        return new HttpResponse($json, $statusCode, $headers);
    }

    public function extractDataFromRequest(Request $request)
    {
        if (in_array($request->getContentType(), ['json', 'application/json']) === true) {
            $input = (array) json_decode($request->getContent(), true);
        } else {
            $input = $request->request->all();
        }

        $transformMapping = $this->getCurrentAction()->getTransformMapping();
        $out = [];

        // process values, set empty strings to null and convert data types
        foreach ($input as $k => $v ) {
            $field = $transformMapping->findField($k, SetterField::OPERATION);

            if ($field !== null) {
                if (is_string($v) === true) {
                    $v = preg_replace('/(^\s+)|(\s+$)/us', '', $x);

                    if (mb_strlen($x) === 0) {
                        $v = null;
                    }
                }

                if ($v !== null) {
                    $v = Helper::processValue($v, $field->getDataType());
                }

                $out[$k] = $v;
            }
        }

        return $out;
    }

    /**
     * @return \Rested\Definition\ActionDefinition
     */
    public function getCurrentAction()
    {
        return $this->getCurrentContext()->getAction();
    }

    /**
     * @return \Rested\Http\Context
     */
    public function getCurrentContext()
    {
        $request = $this->getCurrentRequest();

        return $this
            ->getRestedService()
            ->resolveContextFromRequest($request, $this)
        ;
    }

    public function handle($id)
    {
        $request = $this->getCurrentRequest();
        $controller = $request->get('_rested')['controller'];
        $action = $this->getCurrentContext()->getAction();

        // for this to happen, the user must not have access to the action
        if ($action === null) {
            $this->abort(HttpResponse::HTTP_UNAUTHORIZED);
        }

        $validatable = [
            HttpRequest::METHOD_DELETE,
            HttpRequest::METHOD_PATCH,
            HttpRequest::METHOD_POST,
            HttpRequest::METHOD_PUT,
        ];

        if (in_array($request->getMethod(), $validatable) === true) {
            $this->validate($request);
        }

        return call_user_func_array([$this, $controller], func_get_args());
    }

    public function validate(Request $request)
    {
        $action = $this->getCurrentAction();
        $transform = $action->getTransform();
        $transformMapping = $action->getTransformMapping();
        $input = $this->extractDataFromRequest($request);

        $messages = $transform->validate($transformMapping, $input);

        if (sizeof($messages) > 0) {
            $this->abort(HttpResponse::HTTP_UNPROCESSABLE_ENTITY, [
                'validation_messages' => $messages,
            ]);
        }
    }
}
