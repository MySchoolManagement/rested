<?php
namespace Rested\Http;

use Rested\Definition\ActionDefinition;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\FactoryInterface;
use Rested\RestedResourceInterface;
use Rested\RestedServiceInterface;
use Rested\UrlGeneratorInterface;

class InstanceResponse extends Response
{

    public function __construct(
        RestedServiceInterface $restedService,
        UrlGeneratorInterface $urlGenerator,
        CompiledResourceDefinitionInterface $resourceDefinition,
        $href,
        array $data,
        $instance = null
    )
    {
        // FIXME: remove the need to pass in an instance of the object to create actions from

        parent::__construct($restedService, $urlGenerator, $href, $data);

        $this->addActions($resourceDefinition, [
            ActionDefinition::TYPE_DELETE,
            ActionDefinition::TYPE_INSTANCE_AFFORDANCE,
            ActionDefinition::TYPE_UPDATE
        ], $instance);
    }
}
