<?php
namespace Rested;

use Symfony\Component\HttpFoundation\Request as Request;

interface RestedServiceInterface
{

    public function addResource($class);

    /**
     * @return \Nocarrier\Hal
     */
    public function execute($url, $method = Request::METHOD_GET, $data = [], &$statusCode = null);

    /**
     * @return null|\Rested\Definition\Compiled\CompiledActionDefinitionInterface
     */
    public function findActionByRouteName($routeName);

    /**
     * @return string[]
     */
    public function getResources();

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Rested\ResourceInterface $resource
     * @return \Rested\Http\Context
     */
    public function resolveContextFromRequest(Request $request, ResourceInterface $resource);
}
