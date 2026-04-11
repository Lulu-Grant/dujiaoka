<?php

namespace App\Service;

class AdminShellResourceRegistry
{
    public static function navigationSectionLabel(): string
    {
        return 'First Batch';
    }

    public static function definitions(): array
    {
        return [
            'goods-group' => [
                'nav_label' => '商品分类管理',
                'index_title' => '商品分类管理',
                'index_description' => '这是第一批后台迁移样板页。当前使用普通 Laravel 控制器、服务和 Blade 渲染，不再依赖 Dcat Grid。',
                'show_title' => '商品分类详情',
                'show_description' => '这是商品分类页的详情样板。后续真正替换后台壳时，可以直接照着这组字段合同迁移。',
                'controller' => \App\Http\Controllers\AdminShell\GoodsGroupShellController::class,
                'service' => \App\Service\AdminShellGoodsGroupPageService::class,
                'uri' => 'v2/goods-group',
                'uses_scope' => true,
            ],
            'emailtpl' => [
                'nav_label' => '邮件模板管理',
                'index_title' => '邮件模板管理',
                'index_description' => '这是第二张后台壳样板页。当前列表、筛选和详情都通过普通 Laravel 控制器与 Blade 组合，不再依赖 Dcat Grid/Show。',
                'show_title' => '邮件模板详情',
                'show_description' => '这张详情页用于固定邮件模板的字段合同，后续新后台壳可以直接复用。',
                'controller' => \App\Http\Controllers\AdminShell\EmailTemplateShellController::class,
                'service' => \App\Service\AdminShellEmailTemplatePageService::class,
                'uri' => 'v2/emailtpl',
                'uses_scope' => false,
            ],
            'pay' => [
                'nav_label' => '支付通道管理',
                'index_title' => '支付通道管理',
                'index_description' => '这是第一批后台迁移的第三张样板页。支付通道的生命周期、支付方式、支付场景都直接复用现有 presenter 与模型映射。',
                'show_title' => '支付通道详情',
                'show_description' => '这张详情页固定了支付通道的展示合同，后续迁移编辑页时可以直接在这套壳上扩展。',
                'controller' => \App\Http\Controllers\AdminShell\PayShellController::class,
                'service' => \App\Service\AdminShellPayPageService::class,
                'uri' => 'v2/pay',
                'uses_scope' => true,
            ],
            'coupon' => [
                'nav_label' => '优惠码管理',
                'index_title' => '优惠码管理',
                'index_description' => '这是第二批后台迁移的第一张样板页。优惠码的使用状态、启用状态和关联商品展示都已经通过普通 Laravel 服务和 Blade 接管。',
                'show_title' => '优惠码详情',
                'show_description' => '这张详情页固定了优惠码的列表、状态与关联商品展示合同，后续可以继续在这套壳上扩展编辑能力。',
                'controller' => \App\Http\Controllers\AdminShell\CouponShellController::class,
                'service' => \App\Service\AdminShellCouponPageService::class,
                'uri' => 'v2/coupon',
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
