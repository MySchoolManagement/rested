<?php
namespace Rested\Http;

use Rested\Definition\ActionDefinition;
use Rested\RestedResourceInterface;
use Rested\UrlGeneratorInterface;

class InstanceResponse extends Response
{

    public function __construct(UrlGeneratorInterface $urlGenerator, RestedResourceInterface $resource, $href, array $item)
    {
        parent::__construct($urlGenerator, $href, $item);

        $this->addActions($resource, [
            ActionDefinition::TYPE_DELETE,
            ActionDefinition::TYPE_INSTANCE_ACTION,
            ActionDefinition::TYPE_UPDATE
        ]);
    }
}
