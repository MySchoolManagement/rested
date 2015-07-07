<?php
namespace Rested;

interface RestedServiceInterface
{

    public function addResource($class);

    /**
     * @return \Nocarrier\Hal
     */
    public function execute($url, $method = 'get', $data = [], &$statusCode = null);

    public function getPrefix();
}