<?php
namespace Rested;

use Nocarrier\Hal;
use Symfony\Component\Form\FormInterface;

abstract class Response
{

    /**
     * List of high-level messages (warnings, errors) about the result of this endpoint.
     *
     * @var array
     */
    public $messages = [];

    /**
     * Adds form data to the result.
     */
    public function addForm(FormInterface $form)
    {
        $this->validation = [
            'is_valid' => $form->isValid(),
            'errors'   => []
        ];

        $this->getErrorMessages($form);
    }

    public function addMessage($type, $message)
    {
        $this->messages[] = [
            'type'    => $type,
            'message' => $message
        ];
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    private function getErrorMessages(FormInterface $form)
    {
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if ($child->isValid() == false) {
                    $this->getErrorMessages($child);
                }
            }
        } else {
            foreach ($form->getErrors() as $error) {
                $this->validation['errors'][$form->getName()] = $error->getMessage();
            }
        }
    }

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

        return $hal->asJson(true);
    }

}

class InstanceResponse extends Response
{

    /**
     * @var \stdClass
     */
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function toJson(AbstractResource $resource)
    {
        $hal = new Hal('/abc');
        $hal->setData([$this->item]);
    }
}