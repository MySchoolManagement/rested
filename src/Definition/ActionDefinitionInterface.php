<?php
namespace Rested\Definition;

use Rested\Transforms\TransformInterface;
use Rested\Transforms\TransformMappingInterface;

interface ActionDefinitionInterface
{

    /**
     * @return \Rested\Definition\Parameter
     */
    public function addToken($name, $type, $defaultValue = null, $description = null);

    /**
     * @return string
     */
    public function getAcceptedContentType();

    /**
     * @return null|callable
     */
    public function getAffordanceAvailabilityCallback();

    /**
     * @return string
     */
    public function getControllerName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getHttpMethod();

    /**
     * @return string
     */
    public function getSummary();

    /**
     * @return \Rested\Definition\Parameter[]
     */
    public function getTokens();

    /**
     * @return \Rested\Transforms\TransformInterface
     */
    public function getTransform();

    /**
     * @return \Rested\Transforms\TransformMappingInterface
     */
    public function getTransformMapping();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return bool
     */
    public function isAffordanceAvailable($instance = null);

    /**
     * @return $this
     */
    public function setAffordanceAvailabilityCallback($callback);

    /**
     * @return $this
     */
    public function setControllerName($name);

    /**
     * @return $this
     */
    public function setAcceptedContentType($mimeType);

    /**
     * @return $this
     */
    public function setDescription($value);

    /**
     * @return $this
     */
    public function setHttpMethod($method);

    /**
     * Should the Id be appended to the Uri?
     *
     * @param bool $value
     * @return $this
     */
    public function setShouldAppendId($value);

    /**
     * @param \Rested\Transforms\TransformInterface $transform
     * @return $this
     */
    public function setTransform(TransformInterface $transform);

    /**
     * @param \Rested\Transforms\TransformMappingInterface $transformMapping
     * @return $this
     */
    public function setTransformMapping(TransformMappingInterface $transformMapping);

    /**
     * @return $this
     */
    public function setSummary($value);

    /**
     * Should the Id be appended to the Uri?
     *
     * @return bool
     */
    public function shouldAppendId();
}
