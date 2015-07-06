<?php
namespace Rested;

interface RestedServiceInterface
{

    public function addResource($class);

    public function execute($url, $method = 'get', $data = [], &$statusCode = null);

    public function getPrefix();
}