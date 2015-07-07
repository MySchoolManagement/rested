<?php
namespace Rested\Http;

use Nocarrier\Hal;
use Rested\Definition\ActionDefinition;
use Rested\RestedResourceInterface;
use Rested\UrlGeneratorInterface;

abstract class Response extends Hal
{

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, $uri = null, $data = [])
    {
        parent::__construct($uri, $data);

        $this->urlGenerator = $urlGenerator;
    }

    protected function addActions(RestedResourceInterface $resource, array $which)
    {
        $def = $resource->getDefinition();
        $actions = $def->filterActionsForAccess();
        $links = [];

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

                $f = [
                    'name' => $field->getName(),
                    'type' => $field->getType(),
                ];

                $rel = $field->getRel();

                if (mb_strlen($rel) > 0) {
                    $f['rel'] = $rel;
                }

                $fields[] = $f;
                $links = array_merge($links, $model->getLinks());
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
                'fields' => $fields
            ];
        }

        foreach ($links as $rel => $route) {
            $this->addLink($rel, $this->urlGenerator->generate($route));
        }
    }
}
