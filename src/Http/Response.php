<?php
namespace Rested\Http;

use Mockery\Generator\Parameter;
use Nocarrier\Hal;
use Rested\Definition\ActionDefinition;
use Rested\Definition\Field;
use Rested\FactoryInterface;
use Rested\Helper;
use Rested\RestedResourceInterface;
use Rested\Security\AccessVoter;
use Rested\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class Response extends Hal
{

    private $factory;

    private $urlGenerator;

    public function __construct(FactoryInterface $factory, UrlGeneratorInterface $urlGenerator, $uri = null, $data = [])
    {
        parent::__construct($uri, $data);

        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;

        $this->addLink('self', $uri);
    }

    protected function addActions(RestedResourceInterface $resource, array $which, $instance = null)
    {
        $def = $resource->getDefinition();
        $actions = $def->filterActionsForAccess($instance);
        $links = [];

        $this->data['_actions'] = [];

        foreach ($actions as $action) {
            if ($action->getType() === ActionDefinition::TYPE_COLLECTION) {
                $this->addFiltersToLink('self', $action);
            }

            if (in_array($action->getType(), $which) === false) {
                continue;
            }

            $links = array_merge($links, $this->addAction($action, $instance));
        }

        foreach ($links as $rel => $route) {
            $this->addRestedLink($rel, $route);
        }
    }

    private function addRestedLink($rel, $route)
    {
        $url = $this->urlGenerator->generate($route);
        $controller = $this->factory->createBasicControllerFromRouteName($route);
        $action = null;

        if ($controller !== null) {
            $action = $controller->getDefinition()->findActionByRouteName($route);
        }

        $this->addLink($rel, $url);
        $this->addFiltersToLink($rel, $action);
    }

    private function addFiltersToLink($rel, ActionDefinition $action= null)
    {
        $filters = [];

        if ($action !== null) {
            $model = $action->getModel();

            foreach ($model->filterFiltersForAccess() as $filter) {
                $filters[$filter->getName()] = [
                    'token' => sprintf('filters[%s]', $filter->getName()),
                    'type' => $filter->getType(),
                ];
            }
        }

        if (sizeof($filters) > 0) {
            $links = $this->getLink($rel);

            if ($links !== false) {
                foreach ($links as $link) {
                    $link->setAttribute('filters', $filters);
                }
            }
        }
    }

    private function addAction(ActionDefinition $action, $instance = null)
    {
        $model = $action->getModel();
        $uri = $this->getUri();
        $fields = [];
        $links = [];
        $operation = $action->getMethod() === Request::METHOD_GET ? AccessVoter::ATTRIB_FIELD_GET : AccessVoter::ATTRIB_FIELD_SET;
        $modelFields = $model->runCustomFieldFilter($model->getFields(), $operation, $instance);

        foreach ($modelFields as $field) {
            if ($field->isModel() === false) {
                continue;
            }

            $fields[] = $this->processField($field);
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
            'type' => $action->getContentType(),
            'fields' => $fields
        ];

        return $links;
    }

    private function processField(Field $field)
    {
        $f = [
            'name' => $field->getName(),
            'type' => $field->getType(),
        ];

        $rel = $field->getRel();

        if (mb_strlen($rel) > 0) {
            $f['rel'] = $rel;
        }

        return $f;
    }
}
