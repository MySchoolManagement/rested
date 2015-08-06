<?php
namespace Rested\Http;

interface ContextInterface
{

    /**
     * @return string
     */
    public function getActionType();

    /**
     * @return null|\Rested\Definition\ActionDefinitionInterface
     */
    public function getAction();

    /**
     * @return string[]
     */
    public function getEmbeds();

    /**
     * @return string[]
     */
    public function getFields();

    /**
     * @return array
     */
    public function getFilters();

    /**
     * @param string $name
     * @return null|string
     */
    public function getFilterValue($name);

    /**
     * @return string
     */
    public function getRouteName();

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @return int
     */
    public function getOffset();

    /**
     * @return \Rested\Definition\Compiled\CompiledResourceDefinitionInterface
     */
    public function getResourceDefinition();

    /**
     * @return bool
     */
    public function wantsEmbed($name);

    /**
     * @return bool
     */
    public function wantsField($name);

    /**
     * @return bool
     */
    public function wantsMetadata();
}
