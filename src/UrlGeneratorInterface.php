<?php
namespace Rested;

interface UrlGeneratorInterface
{

    public function generate($name, $parameters = [], $absolute = true);
}