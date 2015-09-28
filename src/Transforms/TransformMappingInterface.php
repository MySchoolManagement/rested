<?php
namespace Rested\Transforms;

interface TransformMappingInterface
{

    /**
     * @return $this
     */
    public function addEmbed($name, $routeName, array $userData = []);

    /**
     * @return $this
     */
    public function addField($name, $dataType, $getter, $setter, $description, $validationParameters = null, $rel = null);

    /**
     * @return $this
     */
    public function addFilter($name, $dataType, $callable, $description);

    /**
     * @return $this;
     */
    public function addLink($rel, $routeName);

    /**
     * @return string
     */
    public function getCompilerId();

    /**
     * @return \Rested\Definition\Field[]
     */
    public function executeFieldFilterCallback($operation, $instance = null);

    /**
     * @return null|\Rested\Definition\Field
     */
    public function findField($name, $operation);

    /**
     * @return null|\Rested\Definition\Field
     */
    public function findPrimaryKeyField();

    /**
     * @return \Rested\Definition\Embed[]
     */
    public function getEmbeds();

    /**
     * @return null|callable
     */
    public function getFieldFilterCallback();

    /**
     * @return \Rested\Definition\Field[]
     */
    public function getFields($operation);

    /**
     * @return \Rested\Definition\Filter[]
     */
    public function getFilters();

    /**
     * @return array
     */
    public function getLinks();

    /**
     * @return string
     */
    public function getModelClass();

    /**
     * @return string
     */
    public function getPrimaryKeyFieldName();

    /**
     * @param $name
     * @return $this
     */
    public function setPrimaryKeyFieldName($name);

    /**
     * @return $this
     */
    public function setFieldFilterCallback($callback);
}
