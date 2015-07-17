<?php
namespace Rested\Http;

use Rested\Definition\ActionDefinition;
use Rested\FactoryInterface;
use Rested\RestedResourceInterface;
use Rested\UrlGeneratorInterface;

class InstanceResponse extends Response
{

    public function __construct(FactoryInterface $factory, UrlGeneratorInterface $urlGenerator,
        RestedResourceInterface $resource, $href, array $item, $instance = null)
    {
        // FIXME: remove the need to pass in an instance of the object to create actions from

        parent::__construct($factory, $urlGenerator, $href, $item);

        $this->addActions($resource, [
            ActionDefinition::TYPE_DELETE,
            ActionDefinition::TYPE_INSTANCE_ACTION,
            ActionDefinition::TYPE_UPDATE
        ], $instance);
    }
}
