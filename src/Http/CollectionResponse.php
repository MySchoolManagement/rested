<?php
namespace Rested\Http;

use Rested\Definition\ActionDefinition;
use Rested\FactoryInterface;
use Rested\RestedResourceInterface;
use Rested\UrlGeneratorInterface;

class CollectionResponse extends Response
{

    public function __construct(FactoryInterface $factory, UrlGeneratorInterface $urlGenerator,
        RestedResourceInterface $resource, array $items, $total)
    {
        parent::__construct($factory, $urlGenerator, $resource->getCurrentActionUri(), [
            'count' => sizeof($items),
            'total' => $total,
        ]);

        $this->setResource('items', $items);
        $this->addActions($resource, [ActionDefinition::TYPE_CREATE]);
    }
}
