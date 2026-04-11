<?php

namespace App\Service;

class AdminShellResourceRegistry
{
    public static function definitions(): array
    {
        return [
            'goods-group' => [
                'nav_label' => '商品分类管理',
                'controller' => \App\Http\Controllers\AdminShell\GoodsGroupShellController::class,
                'service' => \App\Service\AdminShellGoodsGroupPageService::class,
                'uri' => 'v2/goods-group',
                'uses_scope' => true,
            ],
            'emailtpl' => [
                'nav_label' => '邮件模板管理',
                'controller' => \App\Http\Controllers\AdminShell\EmailTemplateShellController::class,
                'service' => \App\Service\AdminShellEmailTemplatePageService::class,
                'uri' => 'v2/emailtpl',
                'uses_scope' => false,
            ],
            'pay' => [
                'nav_label' => '支付通道管理',
                'controller' => \App\Http\Controllers\AdminShell\PayShellController::class,
                'service' => \App\Service\AdminShellPayPageService::class,
                'uri' => 'v2/pay',
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

    public static function navigationItems(): array
    {
        return collect(static::definitions())->map(function (array $definition, string $resource) {
            return [
                'label' => $definition['nav_label'],
                'href' => admin_url($definition['uri']),
                'active_pattern' => config('admin.route.prefix').'/'.$definition['uri'].'*',
                'resource' => $resource,
            ];
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
