<?php
namespace Rested;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Rested\Http\Middleware\RoleCheckMiddleware;

class RestedServiceProvider extends ServiceProvider
{

    const CONFIG_FILE = __DIR__ . '/../config/rested.php';

    private $router;

    private $resourcesFromServices = [];

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->publishes([
            self::CONFIG_FILE => config_path('rested.php'),
        ]);

        $app = $this->app;

        $this->router = $app->make('router');

        $this->router->middleware('role_check', 'middleware.role_check');

        if ($this->app->routesAreCached() === false) {
            // add some core resources
            $this->addResource('Rested\Resources\EntrypointResource');

            $this->processResources();
        }
    }

    public function getPrefix()
    {
        return config('rested.prefix');
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(self::CONFIG_FILE, 'rested');

        $app = $this->app;

        $app['rested'] = $app->instance('Rested\RestedServiceProvider', $this);

        $app['middleware.role_check'] = $app->share(function($app) {
            return new RoleCheckMiddleware($app['security.authorization_checker']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['rested', 'middleware.role_check'];
    }

    public function addResource($class)
    {
        $this->resourcesFromServices[] = $class;
    }

    private function processResources()
    {
        $resources = array_merge(config('rested.resources'), $this->resourcesFromServices);
        $prefix = $this->getPrefix();
        $router = $this->router;

        $router->group([], function() use ($router, $resources) {
            foreach ($resources as $class) {
                $this->addRoutesFromResourceController($router, $class);
            }
        });
    }

    private function addRoutesFromResourceController(Router $router, $class)
    {
        $obj = new $class();
        $def = $obj->getDefinition();

        foreach ($def->getActions() as $action) {
            if (($href = $action->getUrl()) === null) {
                continue;
            }

            $routeName = $action->getRouteName();
            $roleNames = $action->getRoleNames();
            $callable = sprintf('%s@%s', $class, $action->getCallable());
            $route = $router->{$action->getVerb()}($href, [
                'as' => $routeName,
                'middleware' => 'role_check',
                'rested_type' => $action->getType(),
                'roles' => $roleNames,
                'uses' => $callable,
            ]);


            // @todo: permissions
            // add permission requirements to the route if there are any
            /*if (sizeof($action['required_permission']) > 0) {
                $controller->secure($action['required_permission']);
            }*/

            //$this->routes->add($routeName, $route);

            /* $actionCacheEntry = [
                 '_rest_callable'      => $action->getCallable(),
                 'href'                => $href,
                 'method'              => $action->getMethod(),
                 'required_permission' => $action->getRequiredPermission(),
                 'tokens'              => []
             ];

             // add constraints and validators to the cache
             foreach ($action->getTokens() as $token) {
                 $actionCacheEntry['tokens'][] = [
                     'name'              => $token->getName(),
                     'default_value'     => $token->getDefaultValue(),
                     'validator_pattern' => $token->acceptAnyValue() ? null : $token->getValidatorPattern(false)
                 ];
             }

             $endpointCacheEntry['actions'][] = $actionCacheEntry;*/
        }

        //$this->cache[] = $endpointCacheEntry;*/
    }
}