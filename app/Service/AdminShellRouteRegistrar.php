<?php

namespace App\Service;

use Illuminate\Routing\Router;

class AdminShellRouteRegistrar
{
    /**
     * @var \App\Service\AdminShellResourceRegistry
     */
    private $registry;

    public function __construct(AdminShellResourceRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function register(Router $router): void
    {
        foreach ($this->registry->all() as $resource => $definition) {
            $controller = $definition['controller'];
            $namePrefix = 'admin-shell.'.$resource;

            foreach ($definition['actions'] ?? [] as $routeDefinition) {
                $this->registerActionRoute($router, $definition['uri'], $controller, $namePrefix, $routeDefinition);
            }

            $router->get('v2/'.$resource, [$controller, 'index'])->name($namePrefix.'.index');
            $router->get('v2/'.$resource.'/{id}', [$controller, 'show'])->name($namePrefix.'.show');
        }
    }

    private function registerActionRoute(
        Router $router,
        string $baseUri,
        string $controller,
        string $namePrefix,
        array $routeDefinition
    ): void {
        $method = strtolower($routeDefinition['method']);
        $uri = trim($baseUri.'/'.ltrim($routeDefinition['uri'], '/'), '/');
        $action = $routeDefinition['action'];
        $controller = $routeDefinition['controller'] ?? $controller;
        $name = $namePrefix.'.'.$routeDefinition['name'];

        $router->{$method}($uri, [$controller, $action])->name($name);
    }
}
