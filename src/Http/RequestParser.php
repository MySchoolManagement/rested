<?php
namespace Rested\Http;

use Rested\Definition\Parameter;

class RequestParser
{

    const DEFAULT_LIMIT = 50;
    const DEFAULT_OFFSET = 0;

    /**
     * @var array
     */
    private $parameters = [
        'embed' => '',
        'fields' => '',
        'filters' => [],
        'limit' => self::DEFAULT_LIMIT,
        'offset' => self::DEFAULT_OFFSET,
    ];

    /**
     * @param array $source
     * @return void
     */
    protected function convertDottedNotation(array &$source)
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

    /**
     * @return Parameter[];
     */
    public function getAcceptedParameters()
    {
        return [
            new Parameter('embed', Parameter::TYPE_STRING, '', 'List of sub-records to embed.'),
            new Parameter('fields', Parameter::TYPE_STRING, '', 'List of fields to provide for each item.'),
            new Parameter('filters', Parameter::TYPE_ARRAY, [], 'List of filters to apply.'),
            new Parameter('limit', Parameter::TYPE_INT, self::DEFAULT_LIMIT, 'How many items are to be fetched?'),
            new Parameter('offset', Parameter::TYPE_INT, self::DEFAULT_OFFSET, 'At what offset should we start fetching items?')
        ];
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $requestUrl
     * @param array $query
     * @return void
     */
    public function parse($requestUrl, array $query = [])
    {
        foreach ($this->getAcceptedParameters() as $parameter) {
            $r = false;
            $name = $parameter->getName();
            $dataType = $parameter->getDataType();

            if (array_key_exists($name, $query) === false) {
                $this->parameters[$name] = $parameter->getDefaultValue();
            } else {
                if (($parameter->getDefaultValue() == null) && (array_key_exists($name, $query) == false)) {
                    $r = false;
                } else {
                    // validate arrays here as builtin validation relies on strings
                    if ($parameter->expects('array') == true) {
                        if (is_array($query[$name]) === true) {
                            $r = true;
                        }
                    } else {
                        $r = preg_match(sprintf('/%s/i', Parameter::getValidatorPattern($dataType)), $query[$name]);
                    }
                }

                if (($r === 0) || ($r === false)) {
                    // FIXME
                    $app->abort(400, sprintf('Bad value for \'%s\', expected \'%s\'.', $name, $dataType));
                }

                $this->parameters[$name] = $this->processValue($query[$name]);
            }
        }

        // comma separated lists but we want arrays
        $this->parameters['embed'] = strlen($this->parameters['embed']) ? explode(',', $this->parameters['embed']) : [];
        $this->parameters['fields'] = strlen($this->parameters['fields']) ? explode(',', $this->parameters['fields']) : [];

        $this->convertDottedNotation($this->parameters['embed']);
        $this->convertDottedNotation($this->parameters['fields']);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function processValue($value)
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

