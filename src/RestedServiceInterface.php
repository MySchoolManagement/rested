<?php
namespace Rested;

use Symfony\Component\HttpFoundation\Request;

interface RestedServiceInterface
{

    public function addResource($class);

    /**
     * @return \Nocarrier\Hal
     */
    public function execute($url, $method = 'get', $data = [], &$statusCode = null);

    /**
     * @return string
     */
    public function getPrefix();

    /**
     * @return string[]
     */
    public function getResources();
}
