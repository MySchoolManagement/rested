<?php
namespace Rested\Http;

class RequestBuilder
{

    /**
     * @var string[]
     */
    protected $embeds = [];

    /**
     * @var string[]
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var string
     */
    protected $path;

    public function __construct($path, array $fields = [], array $filters = [], array $embeds = [], $offset = RequestParser::DEFAULT_OFFSET, $limit = RequestParser::DEFAULT_LIMIT)
    {
        $this->embeds = $embeds;
        $this->fields = $fields;
        $this->filters = $filters;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->path = $path;
    }

    /**
     * @return $this
     */
    public function addEmbed($name)
    {
        $this->embeds[] = $name;

        return $this;
    }

    /**
     * @return $this
     */
    public function addField($field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return $this
     */
    public function addFilter($name, $value)
    {
        $this->filters[$name] = $value;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setLimit($value)
    {
        $this->limit = $value;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setOffset($value)
    {
        $this->offset = $value;

        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {

    }
}
