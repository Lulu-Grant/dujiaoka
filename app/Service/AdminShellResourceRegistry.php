<?php

namespace App\Service;

class AdminShellResourceRegistry
{
    public static function navigationSectionLabel(): string
    {
        return '管理菜单';
    }

    public static function definitions(): array
    {
        return [
            'goods-group' => [
                'nav_label' => '商品分类管理',
                'index_title' => '商品分类管理',
                'index_description' => '管理商品分类、排序和启用状态，方便商品按业务线归档展示。',
                'show_title' => '商品分类详情',
                'show_description' => '查看商品分类的基础信息、启用状态和关联数据。',
                'controller' => \App\Http\Controllers\AdminShell\GoodsGroupShellController::class,
                'service' => \App\Service\AdminShellGoodsGroupPageService::class,
                'uri' => 'v2/goods-group',
                'uses_scope' => true,
                'actions' => [
                    ['method' => 'get', 'uri' => 'create', 'action' => 'create', 'name' => 'create', 'controller' => \App\Http\Controllers\AdminShell\GoodsGroupActionController::class],
                    ['method' => 'post', 'uri' => 'create', 'action' => 'store', 'name' => 'store', 'controller' => \App\Http\Controllers\AdminShell\GoodsGroupActionController::class],
                    ['method' => 'get', 'uri' => '{id}/edit', 'action' => 'edit', 'name' => 'edit', 'controller' => \App\Http\Controllers\AdminShell\GoodsGroupActionController::class],
                    ['method' => 'post', 'uri' => '{id}/edit', 'action' => 'update', 'name' => 'update', 'controller' => \App\Http\Controllers\AdminShell\GoodsGroupActionController::class],
                ],
            ],
            'goods' => [
                'nav_label' => '商品管理',
                'index_title' => '商品管理',
                'index_description' => '管理商品列表、筛选、价格、库存和上下架状态。',
                'show_title' => '商品详情',
                'show_description' => '查看商品分类、价格、库存、配置文本和关联优惠码。',
                'controller' => \App\Http\Controllers\AdminShell\GoodsShellController::class,
                'service' => \App\Service\AdminShellGoodsPageService::class,
                'uri' => 'v2/goods',
                'uses_scope' => true,
                'actions' => [
                    ['method' => 'get', 'uri' => 'create', 'action' => 'create', 'name' => 'create', 'controller' => \App\Http\Controllers\AdminShell\GoodsActionController::class],
                    ['method' => 'post', 'uri' => 'create', 'action' => 'store', 'name' => 'store', 'controller' => \App\Http\Controllers\AdminShell\GoodsActionController::class],
                    ['method' => 'get', 'uri' => 'batch-status', 'action' => 'editBatchStatus', 'name' => 'batch-status', 'controller' => \App\Http\Controllers\AdminShell\GoodsActionController::class],
                    ['method' => 'post', 'uri' => 'batch-status', 'action' => 'updateBatchStatus', 'name' => 'batch-status.update', 'controller' => \App\Http\Controllers\AdminShell\GoodsActionController::class],
                    ['method' => 'get', 'uri' => '{id}/edit', 'action' => 'edit', 'name' => 'edit', 'controller' => \App\Http\Controllers\AdminShell\GoodsActionController::class],
                    ['method' => 'post', 'uri' => '{id}/edit', 'action' => 'update', 'name' => 'update', 'controller' => \App\Http\Controllers\AdminShell\GoodsActionController::class],
                ],
            ],
            'order' => [
                'nav_label' => '订单管理',
                'index_title' => '订单管理',
                'index_description' => '查看订单列表、支付状态、履约状态和人工维护信息。',
                'show_title' => '订单详情',
                'show_description' => '查看订单状态、价格、优惠抵扣、支付信息和附加内容。',
                'controller' => \App\Http\Controllers\AdminShell\OrderShellController::class,
                'service' => \App\Service\AdminShellOrderPageService::class,
                'uri' => 'v2/order',
                'uses_scope' => true,
                'actions' => [
                    ['method' => 'get', 'uri' => 'batch-status', 'action' => 'editBatchStatus', 'name' => 'batch-status', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-status', 'action' => 'updateBatchStatus', 'name' => 'batch-status.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-type', 'action' => 'editBatchType', 'name' => 'batch-type', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-type', 'action' => 'updateBatchType', 'name' => 'batch-type.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-info', 'action' => 'editBatchInfo', 'name' => 'batch-info', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-info', 'action' => 'updateBatchInfo', 'name' => 'batch-info.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-title', 'action' => 'editBatchTitle', 'name' => 'batch-title', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-title', 'action' => 'updateBatchTitle', 'name' => 'batch-title.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-title-prefix', 'action' => 'editBatchTitlePrefix', 'name' => 'batch-title-prefix', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-title-prefix', 'action' => 'updateBatchTitlePrefix', 'name' => 'batch-title-prefix.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-title-suffix', 'action' => 'editBatchTitleSuffix', 'name' => 'batch-title-suffix', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-title-suffix', 'action' => 'updateBatchTitleSuffix', 'name' => 'batch-title-suffix.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-title-trim', 'action' => 'editBatchTitleTrim', 'name' => 'batch-title-trim', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-title-trim', 'action' => 'updateBatchTitleTrim', 'name' => 'batch-title-trim.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-title-collapse-spaces', 'action' => 'editBatchTitleCollapseSpaces', 'name' => 'batch-title-collapse-spaces', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-title-collapse-spaces', 'action' => 'updateBatchTitleCollapseSpaces', 'name' => 'batch-title-collapse-spaces.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => 'batch-reset-search-pwd', 'action' => 'batchResetSearchPassword', 'name' => 'batch-reset-search-pwd', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => 'batch-reset-search-pwd', 'action' => 'updateBatchResetSearchPassword', 'name' => 'batch-reset-search-pwd.update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'get', 'uri' => '{id}/edit', 'action' => 'edit', 'name' => 'edit', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                    ['method' => 'post', 'uri' => '{id}/edit', 'action' => 'update', 'name' => 'update', 'controller' => \App\Http\Controllers\AdminShell\OrderActionController::class],
                ],
            ],
            'emailtpl' => [
                'nav_label' => '邮件模板管理',
                'index_title' => '邮件模板管理',
                'index_description' => '管理邮件通知模板，查看变量、用途和模板内容。',
                'show_title' => '邮件模板详情',
                'show_description' => '查看邮件模板标题、内容、变量和使用状态。',
                'controller' => \App\Http\Controllers\AdminShell\EmailTemplateShellController::class,
                'service' => \App\Service\AdminShellEmailTemplatePageService::class,
                'uri' => 'v2/emailtpl',
                'uses_scope' => false,
                'actions' => [
                    ['method' => 'get', 'uri' => 'create', 'action' => 'create', 'name' => 'create', 'controller' => \App\Http\Controllers\AdminShell\EmailTemplateActionController::class],
                    ['method' => 'post', 'uri' => 'create', 'action' => 'store', 'name' => 'store', 'controller' => \App\Http\Controllers\AdminShell\EmailTemplateActionController::class],
                    ['method' => 'get', 'uri' => '{id}/edit', 'action' => 'edit', 'name' => 'edit', 'controller' => \App\Http\Controllers\AdminShell\EmailTemplateActionController::class],
                    ['method' => 'post', 'uri' => '{id}/edit', 'action' => 'update', 'name' => 'update', 'controller' => \App\Http\Controllers\AdminShell\EmailTemplateActionController::class],
                ],
            ],
            'pay' => [
                'nav_label' => '支付通道管理',
                'index_title' => '支付通道管理',
                'index_description' => '管理支付通道、支付方式、使用场景和启用状态。',
                'show_title' => '支付通道详情',
                'show_description' => '查看支付通道标识、场景、方式、配置摘要和启用状态。',
                'controller' => \App\Http\Controllers\AdminShell\PayShellController::class,
                'service' => \App\Service\AdminShellPayPageService::class,
                'uri' => 'v2/pay',
                'uses_scope' => true,
                'actions' => [
                    ['method' => 'get', 'uri' => 'create', 'action' => 'create', 'name' => 'create', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'create', 'action' => 'store', 'name' => 'store', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-client', 'action' => 'editBatchClient', 'name' => 'batch-client', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-client', 'action' => 'updateBatchClient', 'name' => 'batch-client.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-method', 'action' => 'editBatchMethod', 'name' => 'batch-method', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-method', 'action' => 'updateBatchMethod', 'name' => 'batch-method.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-name', 'action' => 'editBatchName', 'name' => 'batch-name', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-name', 'action' => 'updateBatchName', 'name' => 'batch-name.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-name-prefix', 'action' => 'editBatchNamePrefix', 'name' => 'batch-name-prefix', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-name-prefix', 'action' => 'updateBatchNamePrefix', 'name' => 'batch-name-prefix.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-name-suffix', 'action' => 'editBatchNameSuffix', 'name' => 'batch-name-suffix', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-name-suffix', 'action' => 'updateBatchNameSuffix', 'name' => 'batch-name-suffix.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-name-replace', 'action' => 'editBatchNameReplace', 'name' => 'batch-name-replace', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-name-replace', 'action' => 'updateBatchNameReplace', 'name' => 'batch-name-replace.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-name-trim', 'action' => 'editBatchNameTrim', 'name' => 'batch-name-trim', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-name-trim', 'action' => 'updateBatchNameTrim', 'name' => 'batch-name-trim.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-name-collapse-spaces', 'action' => 'editBatchNameCollapseSpaces', 'name' => 'batch-name-collapse-spaces', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-name-collapse-spaces', 'action' => 'updateBatchNameCollapseSpaces', 'name' => 'batch-name-collapse-spaces.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => 'batch-status', 'action' => 'editBatchStatus', 'name' => 'batch-status', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => 'batch-status', 'action' => 'updateBatchStatus', 'name' => 'batch-status.update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'get', 'uri' => '{id}/edit', 'action' => 'edit', 'name' => 'edit', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                    ['method' => 'post', 'uri' => '{id}/edit', 'action' => 'update', 'name' => 'update', 'controller' => \App\Http\Controllers\AdminShell\PayActionController::class],
                ],
            ],
            'coupon' => [
                'nav_label' => '优惠码管理',
                'index_title' => '优惠码管理',
                'index_description' => '管理优惠码内容、折扣、可用次数、使用状态和关联商品。',
                'show_title' => '优惠码详情',
                'show_description' => '查看优惠码状态、折扣、次数和关联商品。',
                'controller' => \App\Http\Controllers\AdminShell\CouponShellController::class,
                'service' => \App\Service\AdminShellCouponPageService::class,
                'uri' => 'v2/coupon',
                'uses_scope' => true,
                'actions' => [
                    ['method' => 'get', 'uri' => 'create', 'action' => 'create', 'name' => 'create', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'create', 'action' => 'store', 'name' => 'store', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-status', 'action' => 'editBatchStatus', 'name' => 'batch-status', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-status', 'action' => 'updateBatchStatus', 'name' => 'batch-status.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-use', 'action' => 'editBatchUse', 'name' => 'batch-use', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-use', 'action' => 'updateBatchUse', 'name' => 'batch-use.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-discount', 'action' => 'editBatchDiscount', 'name' => 'batch-discount', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-discount', 'action' => 'updateBatchDiscount', 'name' => 'batch-discount.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-ret', 'action' => 'editBatchRet', 'name' => 'batch-ret', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-ret', 'action' => 'updateBatchRet', 'name' => 'batch-ret.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-code', 'action' => 'editBatchCode', 'name' => 'batch-code', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-code', 'action' => 'updateBatchCode', 'name' => 'batch-code.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-code-prefix', 'action' => 'editBatchCodePrefix', 'name' => 'batch-code-prefix', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-code-prefix', 'action' => 'updateBatchCodePrefix', 'name' => 'batch-code-prefix.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-code-suffix', 'action' => 'editBatchCodeSuffix', 'name' => 'batch-code-suffix', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-code-suffix', 'action' => 'updateBatchCodeSuffix', 'name' => 'batch-code-suffix.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-code-replace', 'action' => 'editBatchCodeReplace', 'name' => 'batch-code-replace', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-code-replace', 'action' => 'updateBatchCodeReplace', 'name' => 'batch-code-replace.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-code-trim', 'action' => 'editBatchCodeTrim', 'name' => 'batch-code-trim', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-code-trim', 'action' => 'updateBatchCodeTrim', 'name' => 'batch-code-trim.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => 'batch-code-collapse-spaces', 'action' => 'editBatchCodeCollapseSpaces', 'name' => 'batch-code-collapse-spaces', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => 'batch-code-collapse-spaces', 'action' => 'updateBatchCodeCollapseSpaces', 'name' => 'batch-code-collapse-spaces.update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'get', 'uri' => '{id}/edit', 'action' => 'edit', 'name' => 'edit', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                    ['method' => 'post', 'uri' => '{id}/edit', 'action' => 'update', 'name' => 'update', 'controller' => \App\Http\Controllers\AdminShell\CouponActionController::class],
                ],
            ],
            'carmis' => [
                'nav_label' => '卡密管理',
                'index_title' => '卡密管理',
                'index_description' => '管理卡密内容、销售状态、循环使用标记和关联商品。',
                'show_title' => '卡密详情',
                'show_description' => '查看卡密内容、状态、循环使用标记和关联商品。',
                'controller' => \App\Http\Controllers\AdminShell\CarmisShellController::class,
                'service' => \App\Service\AdminShellCarmisPageService::class,
                'uri' => 'v2/carmis',
                'uses_scope' => true,
                'actions' => [
                    ['method' => 'get', 'uri' => 'batch-loop', 'action' => 'editBatchLoop', 'name' => 'batch-loop', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'post', 'uri' => 'batch-loop', 'action' => 'updateBatchLoop', 'name' => 'batch-loop.update', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'get', 'uri' => 'batch-trim', 'action' => 'editBatchTrim', 'name' => 'batch-trim', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'post', 'uri' => 'batch-trim', 'action' => 'updateBatchTrim', 'name' => 'batch-trim.update', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'get', 'uri' => 'batch-collapse-spaces', 'action' => 'editBatchCollapseSpaces', 'name' => 'batch-collapse-spaces', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'post', 'uri' => 'batch-collapse-spaces', 'action' => 'updateBatchCollapseSpaces', 'name' => 'batch-collapse-spaces.update', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'get', 'uri' => 'batch-replace', 'action' => 'editBatchReplace', 'name' => 'batch-replace', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'post', 'uri' => 'batch-replace', 'action' => 'updateBatchReplace', 'name' => 'batch-replace.update', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'get', 'uri' => 'batch-suffix', 'action' => 'editBatchSuffix', 'name' => 'batch-suffix', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'post', 'uri' => 'batch-suffix', 'action' => 'updateBatchSuffix', 'name' => 'batch-suffix.update', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'get', 'uri' => 'create', 'action' => 'create', 'name' => 'create', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'post', 'uri' => 'create', 'action' => 'store', 'name' => 'store', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'get', 'uri' => '{id}/edit', 'action' => 'edit', 'name' => 'edit', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'post', 'uri' => '{id}/edit', 'action' => 'update', 'name' => 'update', 'controller' => \App\Http\Controllers\AdminShell\CarmiActionController::class],
                    ['method' => 'get', 'uri' => 'import', 'action' => 'create', 'name' => 'import', 'controller' => \App\Http\Controllers\AdminShell\CarmiImportActionController::class],
                    ['method' => 'post', 'uri' => 'import', 'action' => 'store', 'name' => 'import.store', 'controller' => \App\Http\Controllers\AdminShell\CarmiImportActionController::class],
                ],
            ],
            'system-setting' => [
                'nav_label' => '系统设置概览',
                'index_title' => '系统设置概览',
                'index_description' => '集中管理站点基础、品牌、邮件、通知、订单和体验配置。',
                'show_title' => '系统设置详情',
                'show_description' => '按配置分组查看当前系统设置项。',
                'controller' => \App\Http\Controllers\AdminShell\SystemSettingShellController::class,
                'service' => \App\Service\AdminShellSystemSettingPageService::class,
                'uri' => 'v2/system-setting',
                'uses_scope' => false,
                'actions' => [
                    ['method' => 'get', 'uri' => 'base', 'action' => 'editBase', 'name' => 'base', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'post', 'uri' => 'base', 'action' => 'updateBase', 'name' => 'base.update', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'get', 'uri' => 'branding', 'action' => 'editBranding', 'name' => 'branding', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'post', 'uri' => 'branding', 'action' => 'updateBranding', 'name' => 'branding.update', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'get', 'uri' => 'mail', 'action' => 'editMail', 'name' => 'mail', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'post', 'uri' => 'mail', 'action' => 'updateMail', 'name' => 'mail.update', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'get', 'uri' => 'order', 'action' => 'editOrder', 'name' => 'order', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'post', 'uri' => 'order', 'action' => 'updateOrder', 'name' => 'order.update', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'get', 'uri' => 'push', 'action' => 'editPush', 'name' => 'push', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'post', 'uri' => 'push', 'action' => 'updatePush', 'name' => 'push.update', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'get', 'uri' => 'experience', 'action' => 'editExperience', 'name' => 'experience', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                    ['method' => 'post', 'uri' => 'experience', 'action' => 'updateExperience', 'name' => 'experience.update', 'controller' => \App\Http\Controllers\AdminShell\SystemSettingActionController::class],
                ],
            ],
            'email-test' => [
                'nav_label' => '邮件测试概览',
                'index_title' => '邮件测试概览',
                'index_description' => '查看邮件发送配置，并进入测试邮件发送入口。',
                'show_title' => '邮件测试详情',
                'show_description' => '查看邮件测试所需的发信配置和收件信息。',
                'controller' => \App\Http\Controllers\AdminShell\EmailTestShellController::class,
                'service' => \App\Service\AdminShellEmailTestPageService::class,
                'uri' => 'v2/email-test',
                'uses_scope' => false,
                'actions' => [
                    ['method' => 'get', 'uri' => 'send', 'action' => 'create', 'name' => 'send', 'controller' => \App\Http\Controllers\AdminShell\EmailTestActionController::class],
                    ['method' => 'post', 'uri' => 'send', 'action' => 'store', 'name' => 'send.store', 'controller' => \App\Http\Controllers\AdminShell\EmailTestActionController::class],
                ],
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
