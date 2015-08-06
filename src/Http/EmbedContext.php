<?php
namespace Rested\Http;

use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;

class EmbedContext extends Context
{

    /**
     * {@inheritdoc}
     */
    public static function create(CompiledResourceDefinitionInterface $resourceDefinition, ContextInterface $parentContext, $name)
    {
        $parentEmbeds = $parentContext->getEmbeds();
        $parentFields = $parentContext->getFields();
        $parentFilters = $parentContext->getFilters();

        $parameters = [
            'embed' => array_key_exists($name, $parentEmbeds) ? $parentEmbeds[$name] : [],
            'fields' => array_key_exists($name, $parentFields) ? $parentFields[$name] : [],
            'filters' => array_key_exists($name, $parentFilters) ? $parentFilters[$name] : [],
            'limit' => RequestParser::DEFAULT_LIMIT,
            'metadata' => true,
            'offset' => RequestParser::DEFAULT_OFFSET,
        ];

        return new static($parameters, null, null, $resourceDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionType()
    {
        throw new \RuntimeException('This is not valid for an embed context, this may be caused by a bug');
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        throw new \RuntimeException('This is not valid for an embed context, this may be caused by a bug');
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName()
    {
        throw new \RuntimeException('This is not valid for an embed context, this may be caused by a bug');
    }
}
