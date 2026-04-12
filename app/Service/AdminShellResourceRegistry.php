<?php

namespace App\Service;

class AdminShellResourceRegistry
{
    public static function navigationSectionLabel(): string
    {
        return 'Admin Shell';
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
            'goods' => [
                'nav_label' => '商品管理',
                'index_title' => '商品管理',
                'index_description' => '这是后台壳中的复杂资源样板页。当前先承接商品列表、筛选和详情展示合同，为后续迁移编辑与批量动作铺路。',
                'show_title' => '商品详情',
                'show_description' => '这张详情页固定了商品分类、价格、库存、配置文本和关联优惠码等展示合同，后续可以继续扩展编辑能力。',
                'controller' => \App\Http\Controllers\AdminShell\GoodsShellController::class,
                'service' => \App\Service\AdminShellGoodsPageService::class,
                'uri' => 'v2/goods',
                'uses_scope' => true,
            ],
            'order' => [
                'nav_label' => '订单管理',
                'index_title' => '订单管理',
                'index_description' => '这是后台壳中的高上下文业务资源样板页。当前先承接订单列表、筛选和详情展示合同，验证后台壳对订单查询面的承载能力。',
                'show_title' => '订单详情',
                'show_description' => '这张详情页固定了订单状态、价格、优惠抵扣、支付信息和附加内容展示合同，为后续迁移订单动作页提供稳定底座。',
                'controller' => \App\Http\Controllers\AdminShell\OrderShellController::class,
                'service' => \App\Service\AdminShellOrderPageService::class,
                'uri' => 'v2/order',
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
            'carmis' => [
                'nav_label' => '卡密管理',
                'index_title' => '卡密管理',
                'index_description' => '这是第二批后台迁移的第二张样板页。卡密状态、循环使用标记和关联商品展示已经脱离 Dcat Grid，进入普通 Laravel 页面服务。',
                'show_title' => '卡密详情',
                'show_description' => '这张详情页固定了卡密状态和内容展示合同，后续可以在这套壳上继续扩展导入与编辑能力。',
                'controller' => \App\Http\Controllers\AdminShell\CarmisShellController::class,
                'service' => \App\Service\AdminShellCarmisPageService::class,
                'uri' => 'v2/carmis',
                'uses_scope' => true,
            ],
            'system-setting' => [
                'nav_label' => '系统设置概览',
                'index_title' => '系统设置概览',
                'index_description' => '这是第二批后台迁移的配置型页面样板。当前按配置分组展示站点、通知与邮件设置，不再依赖 Dcat Card 页面壳。',
                'show_title' => '系统设置详情',
                'show_description' => '这张详情页按配置分组展示设置项，为后续迁移真正的配置编辑页提供分组合同。',
                'legacy_uri' => 'system-setting',
                'controller' => \App\Http\Controllers\AdminShell\SystemSettingShellController::class,
                'service' => \App\Service\AdminShellSystemSettingPageService::class,
                'uri' => 'v2/system-setting',
                'uses_scope' => false,
            ],
            'email-test' => [
                'nav_label' => '邮件测试概览',
                'index_title' => '邮件测试概览',
                'index_description' => '这是第二批后台迁移的第二张配置型页面样板。当前按表单合同和运行时配置展示邮件测试页面，不再依赖 Dcat Card 页面壳。',
                'show_title' => '邮件测试详情',
                'show_description' => '这张详情页用于固定邮件测试页的表单字段与发信配置合同，为后续迁移真实测试发送入口提供基础。',
                'legacy_uri' => 'email-test',
                'controller' => \App\Http\Controllers\AdminShell\EmailTestShellController::class,
                'service' => \App\Service\AdminShellEmailTestPageService::class,
                'uri' => 'v2/email-test',
                'uses_scope' => false,
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
