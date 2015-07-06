<?php
namespace Rested;

use Nocarrier\Hal;
use Rested\Definition\ActionDefinition;

abstract class Response extends Hal
{

    protected function addActions(RestedResourceInterface $resource, array $which)
    {
        $def = $resource->getDefinition();
        $actions = $def->filterActionsForAccess();

        $this->data['_actions'] = [];

        foreach ($actions as $action) {
            if (in_array($action->getType(), $which) === false) {
                continue;
            }

            $fields = [];
            $model = $action->getModel();
            $uri = $this->getUri();

            foreach ($model->getFields() as $field) {
                if ($field->isModel() === false) {
                    continue;
                }

                $fields[] = [
                    'name' => $field->getName(),
                    'type' => $field->getType(),
                ];
            }

            // TODO: fix this
            if ($action->getType() === ActionDefinition::TYPE_INSTANCE_ACTION) {
                $uri = Helper::makeUrl($uri, $action->getAppendUrl());
            }

            $this->data['_actions'][] = [
                'name' => $action->getName(),
                'href' => $uri,
                'method' => $action->getMethod(),
                'type' => 'application/json',
                'fields' => $fields,
            ];
        }
    }

    /**
     * @return CollectionResponse
     */
    public static function createCollection(RestedResourceInterface $resource, array $items = [], $total = 0)
    {
        return new CollectionResponse($resource, $items, $total);
    }

    /**
     * @return InstanceResponse
     */
    public static function createInstance(RestedResourceInterface $resource, $href, $item)
    {
        return new InstanceResponse($resource, $href, $item);
    }
}

class CollectionResponse extends Response
{

    public function __construct(RestedResourceInterface $resource, array $items, $total)
    {
        parent::__construct($resource->getCurrentActionUri(), [
            'count' => sizeof($items),
            'total' => $total,
        ]);

        $this->setResource('items', $items);
        $this->addActions($resource, [ActionDefinition::TYPE_CREATE]);
    }

}

class InstanceResponse extends Response
{

    public function __construct(RestedResourceInterface $resource, $href, array $item)
    {
        parent::__construct($href, $item);

        $this->addActions($resource, [
            ActionDefinition::TYPE_DELETE,
            ActionDefinition::TYPE_INSTANCE_ACTION,
            ActionDefinition::TYPE_UPDATE
        ]);
    }
}