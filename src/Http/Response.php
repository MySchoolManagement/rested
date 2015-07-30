<?php
namespace Rested\Http;

use Mockery\Generator\Parameter;
use Nocarrier\Hal;
use Rested\Definition\ActionDefinition;
use Rested\Definition\ActionDefinitionInterface;
use Rested\Definition\Compiled\CompiledActionDefinitionInterface;
use Rested\Definition\Compiled\CompiledResourceDefinitionInterface;
use Rested\Definition\Field;
use Rested\Definition\GetterField;
use Rested\Definition\SetterField;
use Rested\FactoryInterface;
use Rested\Helper;
use Rested\RestedResourceInterface;
use Rested\RestedServiceInterface;
use Rested\Security\AccessVoter;
use Rested\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class Response extends Hal
{

    /**
     * @var \Rested\RestedServiceInterface
     */
    protected $restedService;

    /**
     * @var \Rested\UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(
        RestedServiceInterface $restedService,
        UrlGeneratorInterface $urlGenerator,
        $uri = null,
        array $data = [])
    {
        parent::__construct($uri, $data);

        $this->restedService = $restedService;
        $this->urlGenerator = $urlGenerator;

        $this->addLink('self', $uri);
    }

    protected function addActions(CompiledResourceDefinitionInterface $resourceDefinition, array $which, $instance = null)
    {
        $actions = $resourceDefinition->getActions();
        $links = [];

        $this->data['_actions'] = [];

        foreach ($actions as $action) {
            if ($action->getType() === ActionDefinition::TYPE_COLLECTION) {
                $this->addFiltersToLink('self', $action);
            }

            if ((in_array($action->getType(), $which) === false)
                || ($action->isAffordanceAvailable($instance) === false)) {
                continue;
            }

            $links = array_merge($links, $this->addAction($resourceDefinition, $action, $instance));
        }

        foreach ($links as $rel => $routeName) {
            $this->addRestedLink($rel, $routeName);
        }
    }

    protected function addRestedLink($rel, $routeName)
    {
        $action = $this->restedService->findActionByRouteName($routeName);

        if ($action !== null) {
            $this->addLink($rel, $action->getEndpointUrl());
            $this->addFiltersToLink($rel, $action);
        }
    }

    protected function addFiltersToLink($rel, CompiledActionDefinitionInterface $action)
    {
        $filters = [];
        $transformMapping = $action->getTransformMapping();

        foreach ($transformMapping->getFilters() as $filter) {
            $filters[$filter->getName()] = [
                'token' => sprintf('filters[%s]', $filter->getName()),
                'type' => $filter->getDataType(),
            ];
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

    protected function addAction(
        CompiledResourceDefinitionInterface $resourceDefinition,
        CompiledActionDefinitionInterface $action,
        $instance = null)
    {;
        $url = $action->getEndpointUrl();
        $fields = [];
        $links = [];
        $operation = ($action->getHttpMethod() === Request::METHOD_GET) ? GetterField::OPERATION : SetterField::OPERATION;
        $transform = $action->getTransform();
        $transformMapping = $action->getTransformMapping();

        // FIXME: refactor so that this runs when the action is compiled
        $modelFields = $transformMapping->executeFieldFilterCallback($operation, $instance);

        foreach ($modelFields as $field) {
            $fields[] = $this->processField($field);
            $links = array_merge($links, $transformMapping->getLinks());
        }

        if ($action->getType() !== ActionDefinition::TYPE_CREATE) {
            $url = $transform->makeUrlForInstance($resourceDefinition, $instance);

            // FIXME: we should not be making urls
            if ($action->shouldAppendId() === true) {
                $url .= '/' . $action->getId();
            }
        }

        $this->data['_actions'][] = [
            'fields' => $fields,
            'href' => $url,
            'id' => $action->getId(),
            'method' => $action->getHttpMethod(),
            'type' => $action->getAcceptedContentType(),
        ];

        return $links;
    }

    protected function processField(Field $field)
    {
        $f = [
            'name' => $field->getName(),
            'type' => $field->getDataType(),
        ];

        $rel = $field->getRel();

        if (mb_strlen($rel) > 0) {
            $f['rel'] = $rel;
        }

        return $f;
    }
}
