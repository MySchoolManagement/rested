<?php
namespace Rested;

interface RestedResourceInterface
{

    /**
     * @return Rested\Definition\ResourceDefinition
     */
    public function createDefinition();

    /**
     * @return Rested\FactoryInterface
     */
    public function getFactory();
}