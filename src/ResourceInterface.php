<?php
namespace Rested;

use Nocarrier\Hal;
use Symfony\Component\HttpFoundation\Response;

interface ResourceInterface
{

    /**
     * Aborts the request and sends the given message and HTTP status code to the client.
     *
     * @param int $statusCode HTTP status code.
     * @param array $attributes Array of attributes to return in the response.
     *
     * @return void
     */
    public function abort($statusCode, array $attributes = []);

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function done(Hal $response = null, $statusCode = Response::HTTP_OK, $headers = []);

    /**
     * @return \Rested\Definition\ResourceDefinitionInterface
     */
    public static function createResourceDefinition(FactoryInterface $factory);

    /**
     * The action that is currently executing.
     *
     * @return \Rested\Definition\Compiled\CompiledActionDefinitionInterface
     */
    public function getCurrentAction();

    /**
     * Gets the context bound to the current request.
     *
     * @return \Rested\Http\Context
     */
    public function getCurrentContext();

    /**
     * Gets the current request.
     *
     * Note: This is stacked and can change as internal API calls are executed.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getCurrentRequest();

    /**
     * Gets the Rested service.
     *
     * @return \Rested\RestedServiceInterface
     */
    public function getRestedService();
}
