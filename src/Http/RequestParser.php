<?php
namespace Rested\Http;

use Rested\Definition\Parameter;
use Rested\Helper;

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
        $out = [];

        foreach ($source as $k => $v) {
            $ptr = &$out;

            if (mb_strpos($v, '.') !== false) {
                $parts = explode('.', $v);

                for ($i = 0; $i < sizeof($parts); $i++) {
                    $isLast = (sizeof($parts) - 1) === $i;
                    $value = $parts[$i];

                    if ($isLast === true) {
                        $ptr[] = $value;
                    } else {
                        if (array_key_exists($value, $ptr) === false) {
                            $ptr[$value] = [];
                        }

                        $ptr = &$ptr[$value];
                    }
                }
            } else if (mb_strpos($k, '.') !== false) {

            } else {
                $ptr[] = $v;
            }
        }

        $source = $out;
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

                $this->parameters[$name] = Helper::processValue($query[$name], $parameter->getDataType());
            }
        }

        // comma separated lists but we want arrays
        $this->parameters['embed'] = strlen($this->parameters['embed']) ? explode(',', $this->parameters['embed']) : [];
        $this->parameters['fields'] = strlen($this->parameters['fields']) ? explode(',', $this->parameters['fields']) : [];

        $this->convertDottedNotation($this->parameters['embed']);
        $this->convertDottedNotation($this->parameters['fields']);
    }
}
