<?php
namespace Rested\Resources;

use Illuminate\Routing\Router;
use Rested\AbstractResource;
use Rested\Definition\Parameter;
use Rested\Definition\ResourceDefinition;
use Rested\Result;

class EntrypointResource extends AbstractResource
{

    public function createDefinition()
    {
        $def = ResourceDefinition::create('Entrypoint', $this)
                ->setEndpoint('entrypoint')
                ->setSummary('Entrypoint in to the RESTful system')
            ;

        $def->addCollectionAction('collection');

        $def->addInstanceDefinition(ResourceDefinition::class)
                ->add('actions',       Parameter::TYPE_ARRAY,  null,             null, false, 'A controller can contain multiple actions (create, update, read, delete), these are the definitions.')
                ->add('description',   Parameter::TYPE_STRING, 'getDescription', null, false, 'Description of the endpoint.')
                ->add('mapping',       Parameter::TYPE_ARRAY,  null,             null, false, 'Holds information on how fields are mapped to backend values.')
                ->add('name',          Parameter::TYPE_STRING, 'getName',        null, false, 'Name of the module.')
                ->add('provider_name', Parameter::TYPE_STRING, null,             null, false, 'Name of the provider that exposes this endpoint.')
                ->add('provider_tag',  Parameter::TYPE_STRING, null,             null, false, 'Tag of the provider that exposes this endpoint.')
                ->add('summary',       Parameter::TYPE_STRING, 'getSummary',     null, false, 'Summary of the endpoint\'s purpose.')
            ;

        return $def;
    }

    public function collection()
    {
        $result = new Result();
        $result->total = 0;
        $result->data = [];

        // we don't support seeking so whenever we get offset > 0 return empty
        if ($this->getOffset() == 0) {
            foreach (self::$router->getRoutes() as $route) {
                $actionName = $route->getActionName();
                $parts = explode('@', $actionName);
                $class = (sizeof($parts) === 2) ? $parts[0] : null;

                if (($class === null) || (is_subclass_of($class, 'Rested\AbstractResource') === false)) {
                    continue;
                }

                $resource = new $class();
                $def = $resource->getDefinition();
                $actions = $def->getActions();

                // does the user have access to any of the actions in this resource?
                $hasAccess = false;

                foreach ($actions as $action) {
                    // @todo: security
                    /*if ($app['security']->isGranted($action->getRequiredPermission()) == true) {*/
                        $hasAccess = true;
                        /*break;
                    }*/
                }

                if ($hasAccess == false) {
                    continue;
                }

                /*
                // apply href filter against href_collection
                if (($filter = $this->getFilter('href')) !== null) {
                    $found = false;

                    foreach ($actions as $action) {
                        // little bit wrong but its just here to make the API frontend work
                        if (mb_stristr($filter, $action->getUrl()) !== false) {
                            $found = true;
                        }
                    }

                    if ($found == false) {
                        continue;
                    }
                }*/

                $e = $this->export($def);

                /*if ($this->wantsField('provider_name') == true) {
                    $e['provider_name'] = $endpoint->getContext()->getName();
                }

                if ($this->wantsField('provider_tag') == true) {
                    $e['provider_tag'] = $endpoint->getContext()->getTag();
                }

                // actions and mapping are little more than hacks for the API
                if ($this->wantsExpansion('actions') == true) {
                    $e['actions'] = [];

                    foreach ($actions as $action) {
                        // add permission requirements to the route if there are any
                        // @todo: security
                        /*if ($app['security']->isGranted($action->getRequiredPermission()) == false) {
                            continue;
                        }*/
/*
                        $tmp = $action->export($this->getContext());

                        // if($this->wantsField('tokens') == true)
                        {
                            $tmp['tokens'] = [];

                            foreach ($action->getTokens() as $token) {
                                $tmp['tokens'][] = [
                                    'default_value' => $token->getDefaultValue(),
                                    'description'   => $token->getDescription(),
                                    'name'          => $token->getName(),
                                    'types'         => $token->getTypeFriendly()
                                ];
                            }
                        }

                        // if($this->wantsField('model') == true)
                        {
                            $tmp['model'] = [];

                            foreach ($action->getInputs() as $input) {
                                $tmp['model'][] = [
                                    'default_value' => $input->getDefaultValue(),
                                    'description'   => $input->getDescription(),
                                    'name'          => $input->getName(),
                                    'is_required'   => $input->isRequired(),
                                    'types'         => $input->getTypeFriendly()
                                ];
                            }
                        }

                        $e['actions'][] = $tmp;
                    }
                }

                if ($this->wantsExpansion('mapping') == true) {
                    $e['mapping'] = [
                        'fields'  => [],
                        'filters' => []
                    ];

                    $mapping = $endpoint->getExportMapping();

                    if ($mapping !== null) {
                        foreach ($mapping->getFields() as $field) {
                            // TODO: security
                            /*if ($app['security']->isGranted($field->getRequiredPermission()) == false) {
                                continue;
                            }*/
/*
                            $e['mapping']['fields'][] = [
                                'description'         => $field->getDescription(),
                                'name'                => $field->getName(),
                                'required_permission' => $field->getRequiredPermission(),
                                'type'                => $field->getType()
                            ];
                        }

                        foreach ($mapping->getFilters() as $filter) {
                            // TODO: security
                            /*if ($app['security']->isGranted($filter->getRequiredPermission()) == false) {
                                continue;
                            }*//*

                            $e['mapping']['filters'][] = [
                                'description' => $filter->getDescription(),
                                'name'        => $filter->getName(),
                                'type'        => $filter->getType()
                            ];
                        }
                    }
                }*/

                $result->data[] = $e;
            }
        }

        $result->count = $result->total = sizeof($result->data);

        return $this->done($result);
    }
}