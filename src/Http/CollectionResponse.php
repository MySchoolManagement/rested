<?php
namespace Rested\Http;

use Rested\Definition\ActionDefinition;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\ResourceInterface;
use Rested\RestedServiceInterface;
use Rested\UrlGeneratorInterface;

class CollectionResponse extends Response
{

    public function __construct(
        RestedServiceInterface $restedService,
        UrlGeneratorInterface $urlGenerator,
        CompiledResourceDefinitionInterface $resourceDefinition,
        ResourceInterface $resource,
        ContextInterface $context,
        $href,
        array $items,
        $total)
    {
        $count = sizeof($items);

        parent::__construct($restedService, $urlGenerator, $resource, $context, $href, [
            'count' => $count,
            'total' => ($total !== null) ? $total : $count,
        ]);

        $this->setResource('items', $items);
        $this->addActions($resourceDefinition, [ActionDefinition::TYPE_CREATE]);
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
