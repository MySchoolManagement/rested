<?php
namespace Rested;

use Nocarrier\Hal;
use Rested\Definition\ActionDefinition;
use Symfony\Component\Form\FormInterface;

abstract class Response extends Hal
{

    protected function addActions(AbstractResource $resource, array $which)
    {
        $def = $resource->getDefinition();
        $actions = $def->getActions();

        $this->data['_actions'] = [];

        foreach ($actions as $action) {
            if (in_array($action->getType(), $which) === false) {
                continue;
            }

            $fields = [];
            $model = $action->getModel();

            foreach ($model->getFields() as $field) {
                if ($field->isModel() === false) {
                    continue;
                }

                $fields[] = [
                    'name' => $field->getName(),
                    'type' => $field->getType(),
                ];
            }

            $this->data['_actions'][] = [
                'name' => $action->getName(),
                'href' => $this->getUri(),
                'method' => $action->getVerb(),
                'type' => 'application/json',
                'fields' => $fields,
            ];
        }
    }

    /**
     * @return CollectionResponse
     */
    public static function createCollection(AbstractResource $resource, array $items = [], $total = 0)
    {
        return new CollectionResponse($resource, $items, $total);
    }

    /**
     * @return InstanceResponse
     */
    public static function createInstance(AbstractResource $resource, $href, $item)
    {
        return new InstanceResponse($resource, $href, $item);
    }
}

class CollectionResponse extends Response
{

    public function __construct(AbstractResource $resource, array $items, $total)
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

    public function __construct(AbstractResource $resource, $href, array $item)
    {
        parent::__construct($href, $item);

        $this->addActions($resource, [
            ActionDefinition::TYPE_DELETE,
            ActionDefinition::TYPE_UPDATE
        ]);
    }
}