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
     * @return \Rested\Definition\Compiled\CompiledResourceDefinitionInterface
     */
    public function getResourceDefinition();

    /**
     * @return string[]
     */
    public function getFields();

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
     * @return bool
     */
    public function wantsEmbed($name);

    /**
     * @return bool
     */
    public function wantsField($name);
}
