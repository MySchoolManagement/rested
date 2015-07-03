<?php
namespace Rested;

use Nocarrier\Hal;
use Symfony\Component\Form\FormInterface;

abstract class Response
{

    public abstract function toJson(AbstractResource $resource);

    /**
     * @return CollectionResponse
     */
    public static function createCollection(array $items = [], $total = 0)
    {
        return new CollectionResponse($items, $total);
    }

    /**
     * @return InstanceResponse
     */
    public static function createInstance($item)
    {
        return new InstanceResponse($item);
    }
}

class CollectionResponse extends Response
{

    /**
     * @var int
     */
    public $count = 0;

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var \stdClass[]
     */
    public $items = [];

    public function __construct(array $items, $total)
    {
        $this->count = sizeof($items);
        $this->total = (int) $total;
        $this->items = $items;
    }

    public function toJson(AbstractResource $resource)
    {
        $hal = new Hal($resource->getCurrentActionUri());
        $hal->setData([
            'count' => $this->count,
            'total' => $this->total,
        ]);
        $hal->setResource('items', $this->items);

        return $hal->asJson();
    }

}

class InstanceResponse extends Response
{

    /**
     * @var \stdClass
     */
    public $item;

    public function __construct(Hal $item)
    {
        $this->item = $item;
    }

    public function toJson(AbstractResource $resource)
    {
        return $this->item->asJson();
    }
}