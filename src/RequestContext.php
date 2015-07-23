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
        'embed' => [],
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

    public function getFilterValue($name)
    {
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

    public function getLimit()
    {
        return $this->getParameterValue('limit');
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOffset()
    {
        return $this->getParameterValue('offset');
    }

    public function getParameterValue($key)
    {
        if (array_key_exists($key, $this->parameters) === true) {
            return $this->parameters[$key];
        }

        return null;
    }

    /**
     * @return Parameter[]
     */
    public function getReadParameters()
    {
        $p = [
            new Parameter('embed',   Parameter::TYPE_STRING, '', 'List of sub-records to embed.'),
            new Parameter('fields',  Parameter::TYPE_STRING, '', 'List of fields to provide for each item.'),
            new Parameter('filters', Parameter::TYPE_ARRAY,  [], 'List of filters to apply.'),
            new Parameter('limit',   Parameter::TYPE_INT,    50, 'How many items are to be fetched?'),
            new Parameter('offset',  Parameter::TYPE_INT,    0,  'At what offset should we start fetching items?')
        ];

        return $p;
    }

    /**
     * @return \Illuminate\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function getResource()
    {
        return $this->resource;
    }

    private function init()
    {
        $data = $this->request->query->all();

        foreach ($this->getReadParameters() as $parameter) {
            $r = false;
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (array_key_exists($name, $data) === false) {
                $this->parameters[$name] = $parameter->getDefaultValue();
            } else {
                if (($parameter->getDefaultValue() == null) && (array_key_exists($name, $data) == false)) {
                    $r = false;
                } else {
                    // validate arrays here as builtin validation relies on strings
                    if ($parameter->expects('array') == true) {
                        if (is_array($data[$name]) === true) {
                            $r = true;
                        }
                    } else {
                        $r = preg_match(sprintf('/%s/i', Parameter::getValidatorPattern($type)), $data[$name]);
                    }
                }

                if (($r === 0) || ($r === false)) {
                    $app->abort(400, sprintf('Bad value for \'%s\', expected \'%s\'.', $name, $type));
                }

                $this->parameters[$name] = $this->processValue($data[$name]);
            }
        }

        // comma separated lists but we want arrays
        $this->parameters['embed'] = strlen($this->parameters['embed']) ? explode(',', $this->parameters['embed']) : [];
        $this->parameters['fields'] = strlen($this->parameters['fields']) ? explode(',', $this->parameters['fields']) : [];

        $this->convertDottedNotation($this->parameters['fields']);
        $this->convertDottedNotation($this->parameters['filters']);
    }

    private function convertDottedNotation(array &$source)
    {
        foreach ($source as $k => $v) {
            $left = null;
            $index = null;
            $value = null;

            if (mb_strpos($v, '.') !== false) {
                $parts = explode('.', $v);
                $left = $parts[0];
                $index = null;
                $value = $parts[1];
            } else if (mb_strpos($k, '.') !== false) {
                $parts = explode('.', $k);
                $left = $parts[0];
                $index = $parts[1];
                $value = $v;
            } else {
                continue;
            }

            $parts = explode('.', $v);

            if (array_key_exists($left, $source) === false) {
                $source[$left] = [];
            }

            if ($index !== null) {
                $source[$left][$index] = $value;
            } else {
                $source[$left][] = $value;
            }

            unset($source[$k]);
        }
    }

    public function wantsEmbeddable($name)
    {
        if ((in_array('all', $this->parameters['embed']) === true)
            || (in_array($name, $this->parameters['embed']) === true)) {
            return true;
        }

        return false;
    }

    public function wantsField($name)
    {
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
