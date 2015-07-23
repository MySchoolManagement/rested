<?php
namespace Rested;

use Rested\Definition\ActionDefinition;
use Rested\Definition\Parameter;
use Rested\Definition\ResourceDefinition;

abstract class AbstractUrlGenerator
{

    /**
     * Path to mount rested controllers under.
     *
     * @return string
     */
    public abstract function getMountPrefix();

    /**
     * Generates a Url from a route name.
     *
     * @param string $routeName The route name.
     * @param array $parameters Parameters to add to the generated Url.
     * @param true|bool $absolute Should the generated Url be absolute?
     * @return string
     */
    public abstract function route($routeName, array $parameters = [], $absolute = true);

    /**
     * Generates a Url for a given path.
     *
     * @param string $path Path to append to the end of the generated Url.
     * @param true|bool $absolute Should the generated Url be absolute?
     * @return string
     */
    public abstract function url($path, $absolute = true);

    /**
     * Generates a Url for an action.
     *
     * @param \Rested\Definition\ActionDefinition $action Action to generate a Url for.     * @return string
     */
    public function action(ActionDefinition $action)
    {
        $tokens = $action->getTokens();
        $components = array_map(function(Parameter $value) {
            return sprintf('{%s}', $value->getName());
        }, $tokens);

        if ($action->shouldAppendId() === true) {
            $components[] = $action->getId();
        }

        array_unshift($components, $this->resourceDefinition($action->getDefinition()));

        return $this->fromComponents($components);
    }

    /**
     * Generates a Url from the given components.
     *
     * @param array $components List of component strings to join in to a Url.
     * @return string
     */
    public function fromComponents(array $components)
    {
        $u = join('/', $components);
        $u = preg_replace('/\/{2,}/', '/', $u);

        return $this->url($u, false);
    }

    /**
     * Generates a Url for a resource definition.
     *
     * @param \Rested\Definition\ResourceDefinition $resourceDefinition Definition to generate a Url for.
     * @param true|bool $absolute Should the generated Url be absolute?
     * @return string
     */
    public function resourceDefinition(ResourceDefinition $resourceDefinition, $absolute = true)
    {
        return $this->fromComponents([$resourceDefinition->getPath()], $absolute);
    }
}
