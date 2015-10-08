<?php
namespace Rested\Http;

use Rested\Definition\ActionDefinition;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\ResourceInterface;
use Rested\RestedServiceInterface;
use Rested\UrlGeneratorInterface;

class InstanceResponse extends Response
{

    public function __construct(
        RestedServiceInterface $restedService,
        UrlGeneratorInterface $urlGenerator,
        CompiledResourceDefinitionInterface $resourceDefinition,
        ResourceInterface $resource,
        ContextInterface $context,
        $href,
        array $data,
        $instance = null
    )
    {
        parent::__construct($restedService, $urlGenerator, $resource, $context, $href, $data);

        $this->addActions($resourceDefinition, [
            ActionDefinition::TYPE_DELETE,
            ActionDefinition::TYPE_INSTANCE_AFFORDANCE,
            ActionDefinition::TYPE_UPDATE
        ], $instance);
    }
}
