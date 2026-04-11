<?php

namespace App\Service;

class AdminShellResourceRegistry
{
    public static function definitions(): array
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

    public static function permissionExceptPatterns(): array
    {
        return collect(static::definitions())->keys()->map(function ($resource) {
            return 'v2/'.$resource.'*';
        })->values()->all();
    }

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
        return static::definitions();
    }
}
