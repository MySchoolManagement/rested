<?php
namespace Rested;

use Rested\Definition\Parameter;
use Rested\RestedResourceInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestContext
{

    private $request;

    private $resource;

    private $routeName;

    private $parameters = [
        'embeddables' => [],
        'fields' => [],
        'filters' => []
    ];

    public function __construct(Request $request, RestedResourceInterface $resource)
    {
        $this->resource = $resource;
        $this->request = $request;
        $this->actionType = $request->get('_rested_action');
        $this->routeName = $request->get('_rested_route_name');

        $this->init();
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    public function getFields()
    {
        if ($this->hasAccessToField($name) === false) {
            return null;
        }

        return $this->parameters['fields'];
    }

    public function getFilter($name)
    {
        if ($this->hasAccessToFilter($name) === false) {
            return null;
        }

        if ((is_array($this->parameters['filters']) === true)
            && (array_key_exists($name, $this->parameters['filters']) === true)
            && ($this->parameters['filters'][$name] !== null)) {
            return $this->parameters['filters'][$name];
        }

        return null;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setFields(array $fields)
    {
        $this->parameters['fields'] = $fields;
    }

    public function setFilter($name, $value)
    {
        if ($this->hasAccessToFilter($name) === false) {
            return null;
        }

        if ((is_array($this->parameters['filters']) === true)
            && (array_key_exists($name, $this->parameters['filters']) === true)
            && ($this->parameters['filters'][$name] !== null)) {
            $this->parameters['filters'][$name] = $value;
        }
    }

    public function getLimit()
    {
        return $this->getParameter('limit');
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOffset()
    {
        return $this->getParameter('offset');
    }

    public function getParameter($key)
    {
        if (array_key_exists($key, $this->parameters) === true) {
            return $this->parameters[$key];
        }

        return null;
    }

    public function getReadParameters()
    {
        $p = [
            new Parameter('embed',   Parameter::TYPE_ARRAY,  [], 'List of sub-records to embed.'),
            new Parameter('fields',  Parameter::TYPE_STRING, '', 'List of fields to provide for each item.'),
            new Parameter('filters', Parameter::TYPE_ARRAY,  [], 'List of filters to apply.'),
            new Parameter('limit',   Parameter::TYPE_INT,    50, 'How many items are to be fetched?'),
            new Parameter('offset',  Parameter::TYPE_INT,    0,  'At what offset should we start fetching items?')
        ];

        return $p;
    }

    /**
     * @return Illuminate\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function hasAccessToEmbeddable($name)
    {
        // @todo: security
        /*if (($expansion = $this->getExportMapping()->findExpansion($name)) !== null) {
            return $app['security']->isGranted($expansion->getRequiredPermission());
        }*/
        return true;

        return false;
    }

    public function hasAccessToField($name)
    {
        // @todo: security
        /*if (($field = $this->getExportMapping()->findField($name)) !== null) {
            return $app['security']->isGranted($field->getRequiredPermission());
        }*/
        return true;

        return false;
    }

    public function hasAccessToFilter($name)
    {
        // @todo: security
        /*if (($filter = $this->getExportMapping()->findFilter($name)) !== null) {
            return $app['security']->isGranted($filter->getRequiredPermission());
        }*/

        return true;

        return false;
    }

    private function init()
    {
        $data = $this->request->query->all();
        $hasFields = false;

        foreach ($this->getReadParameters() as $parameter) {
            $r = false;

            if (array_key_exists($parameter->getName(), $data) === false) {
                $this->parameters[$parameter->getName()] = $parameter->getDefaultValue();
            } else {
                if (($parameter->getDefaultValue() == null) && (array_key_exists($parameter->getName(), $data) == false)) {
                    $r = false;
                } else {
                    // validate arrays here as builtin validation relies on strings
                    if ($parameter->expects('array') == true) {
                        if (is_array($data[$parameter->getName()]) === true) {
                            $r = true;
                        }
                    } else {
                        $r = preg_match('/'.Parameter::getValidatorPattern($parameter->getType()).'/i', $data[$parameter->getName()]);
                    }
                }

                if (($r === 0) || ($r === false)) {
                    $app->abort(400, sprintf('Bad value for \'%s\', expected \'%s\'.', $parameter->getName(), $parameter->getType()));
                }

                if ($parameter->getName() === 'fields') {
                    $hasFields = true;
                }

                // we do some cheeky processing here to turn true/false in to booleans)
                $this->parameters[$parameter->getName()] = $this->processValue($data[$parameter->getName()]);
            }
        }

        // fields comes as a comma separated list but we want it as an array
        $this->parameters['fields'] = strlen($this->parameters['fields']) ? explode(',', $this->parameters['fields']) : [];
    }

    public function wantsEmbeddable($name)
    {
        if ($this->hasAccessToEmbeddable($name) === false) {
            return false;
        }

        if (is_array($this->parameters['embeddables']) === true) {
            if ((array_key_exists('all', $this->parameters['embeddables']) === true) || (array_key_exists($name, $this->parameters['embeddables']) === true)) {
                return true;
            }
        }

        return false;
    }

    public function wantsField($name)
    {
        if ($this->hasAccessToField($name) === false) {
            return false;
        }

        if (in_array('all', $this->parameters['fields']) === true) {
            return true;
        }

        return in_array($name, $this->parameters['fields']);
    }

    private function processValue($value)
    {
        if (is_array($value) === true) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->processValue($v);
            }
        } else
            if (is_string($value) == true) {
                if ($value === 'true') {
                    return true;
                } else {
                    if ($value === 'false') {
                        return false;
                    }
                }
            }

        return $value;
    }
}
