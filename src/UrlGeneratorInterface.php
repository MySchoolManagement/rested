<?php
namespace Rested;

interface UrlGeneratorInterface
{

    /**
     * Path to mount rested controllers under.
     *
     * @return string
     */
    public function getMountPath();

    /**
     * Generates a Url from a route name.
     *
     * @param string $routeName The route name.
     * @param array $parameters Parameters to add to the generated Url.
     * @param true|bool $absolute Should the generated Url be absolute?
     * @return string
     */
    public function route($routeName, array $parameters = [], $absolute = true);

    /**
     * Generates a Url for a given path.
     *
     * @param string $path Path to append to the end of the generated Url.
     * @param true|bool $absolute Should the generated Url be absolute?
     * @return string
     */
    public function url($path, $absolute = true);
}
