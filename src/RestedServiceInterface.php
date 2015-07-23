<?php
namespace Rested;

use Rested\Definition\ResourceDefinition;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

interface RestedServiceInterface
{

    public function addResource($class);

    /**
     * @return \Nocarrier\Hal
     */
    public function execute($url, $method = HttpRequest::METHOD_GET, $data = [], &$statusCode = null);

    /**
     * @return string[]
     */
    public function getResources();

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Rested\Definition\ResourceDefinition $resourceDefinition
     * @return \Rested\ImmutableContext
     */
    public function resolveContextFromRequest(HttpRequest $request, ResourceDefinition $resourceDefinition);
}
