<?php

namespace App\Service;

class AdminShellResourceRegistry
{
    public function get(string $resource): array
    {
        $resources = $this->all();

        if (!isset($resources[$resource])) {
            abort(404, 'Unknown admin shell resource.');
        }

        return $resources[$resource];
    }

    public function all(): array
    {
        return [
            'goods-group' => [
                'controller' => \App\Http\Controllers\AdminShell\GoodsGroupShellController::class,
                'service' => \App\Service\AdminShellGoodsGroupPageService::class,
                'uses_scope' => true,
            ],
            'emailtpl' => [
                'controller' => \App\Http\Controllers\AdminShell\EmailTemplateShellController::class,
                'service' => \App\Service\AdminShellEmailTemplatePageService::class,
                'uses_scope' => false,
            ],
            'pay' => [
                'controller' => \App\Http\Controllers\AdminShell\PayShellController::class,
                'service' => \App\Service\AdminShellPayPageService::class,
                'uses_scope' => true,
            ],
        ];
    }
}
