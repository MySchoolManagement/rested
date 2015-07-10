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

    /**
     * {@inheritdoc}
     */
    public function asJson($pretty = false, $encode = true)
    {
        $data = parent::asJson($pretty, false);

        // enforce _embedded.items for collections
        if ((array_key_exists('_embedded', $data) === false)
            || (array_key_exists('items', $data['_embedded']) === false)) {
            $data['_embedded']['items'] = [];
        }

        return json_encode($data);
    }
}
