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

            $router->get('v2/'.$resource, [$controller, 'index'])->name($namePrefix.'.index');
            $router->get('v2/'.$resource.'/{id}', [$controller, 'show'])->name($namePrefix.'.show');
        }
    }
}
