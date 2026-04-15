<?php

namespace Tests\Unit;

use App\Models\Emailtpl;
use App\Models\GoodsGroup;
use App\Models\Goods;
use App\Models\Coupon;
use App\Models\Carmis;
use App\Models\Pay;
use App\Models\Order;
use App\Service\AbstractAdminShellPageService;
use App\Service\AdminShellCarmisPageService;
use App\Service\AdminShellCouponPageService;
use App\Service\AdminShellEmailTestPageService;
use App\Service\AdminShellSystemSettingPageService;
use App\Service\Contracts\AdminShellPageServiceInterface;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use App\Service\AdminShellEmailTemplatePageService;
use App\Service\AdminShellGoodsPageService;
use App\Service\AdminShellGoodsGroupPageService;
use App\Service\AdminShellOrderPageService;
use App\Service\AdminShellPayPageService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminShellPageStructureTest extends TestCase
{
    public function test_goods_group_page_service_builds_table_and_detail_items()
    {
        $group = new GoodsGroup();
        $group->forceFill([
            'id' => 101,
            'gp_name' => '默认分类',
            'is_open' => 1,
            'ord' => 9,
            'created_at' => Carbon::parse('2026-04-10 10:00:00'),
            'updated_at' => Carbon::parse('2026-04-10 11:00:00'),
        ]);
        $group->goods_count = 3;

        $service = $this->app->make(AdminShellGoodsGroupPageService::class);
        $this->assertInstanceOf(AdminShellPageServiceInterface::class, $service);
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$group]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$group]), 1, 15));
        $filters = $service->buildFilters(['id' => 101, 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed');
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$group]), 1, 15), ['id' => 101, 'scope' => '']);
        $showPage = $service->buildShowPageData($group, 'trashed');
        $items = $service->detailItems($group);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/goods-group?id=101&scope=trashed', 'GET'));

        $this->assertSame('商品分类管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
        $this->assertSame('trashed', $requestFilters['scope']);
        $this->assertSame('范围', $filters['fields'][1]['label']);
        $this->assertSame('商品分类详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('商品分类管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('商品分类详情 - 后台壳样板', $showPage->title);
        $this->assertSame('商品分类管理 - 后台壳样板', $indexPage->toViewData()['title']);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('分类名称', $table['headers'][1]);
        $this->assertStringContainsString('默认分类', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有商品分类记录。', $table['empty_title']);
        $this->assertSame('分类名称', $items[1]['label']);
        $this->assertSame('默认分类', $items[1]['value']);
    }

    public function test_goods_page_service_builds_table_and_detail_items()
    {
        $goods = new Goods();
        $goods->forceFill([
            'id' => 150,
            'group_id' => 12,
            'gd_name' => '测试商品',
            'gd_description' => '商品简介',
            'gd_keywords' => '关键字',
            'retail_price' => 99,
            'actual_price' => 79,
            'in_stock' => 20,
            'sales_volume' => 5,
            'ord' => 2,
            'buy_limit_num' => 1,
            'buy_prompt' => '提示',
            'description' => '说明',
            'type' => Goods::AUTOMATIC_DELIVERY,
            'wholesale_price_cnf' => '2,70',
            'other_ipu_cnf' => '账号',
            'api_hook' => 'https://example.com/hook',
            'is_open' => 1,
            'created_at' => Carbon::parse('2026-04-10 09:00:00'),
            'updated_at' => Carbon::parse('2026-04-10 10:00:00'),
        ]);
        $goods->carmis_count = 20;
        $goods->setRelation('group', (object) ['gp_name' => '默认分类']);
        $goods->setRelation('coupon', collect([(object) ['coupon' => 'XIGUA-150']]));

        $service = $this->app->make(AdminShellGoodsPageService::class);
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$goods]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$goods]), 1, 15), ['gd_name' => '测试', 'scope' => '']);
        $filters = $service->buildFilters(['gd_name' => '测试', 'type' => Goods::AUTOMATIC_DELIVERY, 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed');
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$goods]), 1, 15), ['gd_name' => '测试', 'scope' => '']);
        $showPage = $service->buildShowPageData($goods, 'trashed');
        $items = $service->detailItems($goods);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/goods?gd_name=test&type=1&scope=trashed', 'GET'));

        $this->assertSame('商品管理', $header['title']);
        $this->assertSame('新建商品', $header['actions'][0]['label']);
        $this->assertSame('批量启停', $header['actions'][1]['label']);
        $this->assertSame('导出文本', $header['actions'][2]['label']);
        $this->assertSame('导出 CSV', $header['actions'][3]['label']);
        $this->assertStringContainsString('export=text', $header['actions'][2]['href']);
        $this->assertStringContainsString('export=csv', $header['actions'][3]['href']);
        $this->assertSame('test', $requestFilters['gd_name']);
        $this->assertSame('商品类型', $filters['fields'][2]['label']);
        $this->assertSame('商品详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('商品管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('商品详情 - 后台壳样板', $showPage->title);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('商品名称', $table['headers'][1]);
        $this->assertStringContainsString('测试商品', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有商品记录。', $table['empty_title']);
        $this->assertSame('所属分类', $items[4]['label']);
        $this->assertSame('默认分类', $items[4]['value']);
    }

    public function test_email_template_page_service_builds_table_and_detail_items()
    {
        $template = new Emailtpl();
        $template->forceFill([
            'id' => 202,
            'tpl_name' => '发货通知',
            'tpl_token' => 'deliver_notice',
            'tpl_content' => 'hello',
            'created_at' => Carbon::parse('2026-04-10 12:00:00'),
            'updated_at' => Carbon::parse('2026-04-10 13:00:00'),
        ]);

        $service = $this->app->make(AdminShellEmailTemplatePageService::class);
        $this->assertInstanceOf(AdminShellPageServiceInterface::class, $service);
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$template]), 1, 15)
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$template]), 1, 15), ['tpl_name' => '发货', 'tpl_token' => 'deliver_notice']);
        $filters = $service->buildFilters(['tpl_name' => '发货', 'tpl_token' => 'deliver_notice']);
        $showHeader = $service->buildShowHeader();
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$template]), 1, 15), ['tpl_name' => '发货']);
        $showPage = $service->buildShowPageData($template);
        $items = $service->detailItems($template);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/emailtpl?tpl_name=test&tpl_token=deliver_notice', 'GET'));

        $this->assertSame('邮件模板管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
        $this->assertSame('新建邮件模板', $header['actions'][1]['label']);
        $this->assertSame('预览样例模板', $header['actions'][2]['label']);
        $this->assertSame('导出当前筛选摘要', $header['actions'][3]['label']);
        $this->assertStringContainsString('export=summary', $header['actions'][3]['href']);
        $this->assertStringContainsString('tpl_name=%E5%8F%91%E8%B4%A7', $header['actions'][3]['href']);
        $this->assertStringContainsString('tpl_token=deliver_notice', $header['actions'][3]['href']);
        $this->assertSame('deliver_notice', $requestFilters['tpl_token']);
        $this->assertSame('邮件标题', $filters['fields'][1]['label']);
        $this->assertSame('邮件模板详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('邮件模板管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('邮件模板详情 - 后台壳样板', $showPage->title);
        $this->assertSame('邮件模板管理 - 后台壳样板', $indexPage->toViewData()['title']);
        $this->assertSame(admin_url('v2/emailtpl'), $showHeader['actions'][0]['href']);
        $this->assertSame('邮件标题', $table['headers'][1]);
        $this->assertStringContainsString('发货通知', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有邮件模板记录。', $table['empty_title']);
        $this->assertSame('邮件内容', $items[3]['label']);
        $this->assertSame('hello', $items[3]['value']);
    }

    public function test_pay_page_service_builds_table_and_detail_items()
    {
        $pay = new Pay();
        $pay->forceFill([
            'id' => 303,
            'pay_name' => 'Stripe',
            'pay_check' => 'stripe',
            'merchant_id' => 'merchant-303',
            'merchant_key' => 'secret-key-303',
            'merchant_pem' => 'secret-pem-303',
            'pay_method' => 2,
            'pay_client' => 1,
            'is_open' => 1,
            'created_at' => Carbon::parse('2026-04-10 14:00:00'),
            'updated_at' => Carbon::parse('2026-04-10 15:00:00'),
        ]);

        $service = $this->app->make(AdminShellPayPageService::class);
        $this->assertInstanceOf(AdminShellPageServiceInterface::class, $service);
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$pay]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$pay]), 1, 15));
        $filters = $service->buildFilters(['pay_check' => 'stripe', 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed', $pay);
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$pay]), 1, 15), ['pay_check' => 'stripe', 'scope' => '']);
        $showPage = $service->buildShowPageData($pay, 'trashed');
        $items = $service->detailItems($pay);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/pay?pay_check=stripe&pay_name=Stripe&scope=trashed', 'GET'));

        $this->assertSame('支付通道管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
        $this->assertSame('批量启停通道', $header['actions'][1]['label']);
        $this->assertSame('批量切换场景', $header['actions'][2]['label']);
        $this->assertSame('新建支付通道', $header['actions'][3]['label']);
        $this->assertSame('导出结构化 CSV', $header['actions'][4]['label']);
        $this->assertSame('导出当前筛选', $header['actions'][5]['label']);
        $this->assertStringContainsString('export=csv', $header['actions'][4]['href']);
        $this->assertStringContainsString('export=txt', $header['actions'][5]['href']);
        $this->assertSame('Stripe', $requestFilters['pay_name']);
        $this->assertSame('支付标识', $filters['fields'][1]['label']);
        $this->assertSame('支付通道详情', $showHeader['title']);
        $this->assertStringContainsString('密钥字段已脱敏', $showHeader['meta']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('支付通道管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('支付通道详情 - 后台壳样板', $showPage->title);
        $this->assertSame('支付通道管理 - 后台壳样板', $indexPage->toViewData()['title']);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('编辑通道', $showHeader['actions'][1]['label']);
        $this->assertSame('支付名称', $table['headers'][1]);
        $this->assertStringContainsString('Stripe', $table['rows'][0][1]);
        $this->assertStringContainsString('编辑通道', $table['rows'][0][8]);
        $this->assertSame('当前条件下没有支付通道记录。', $table['empty_title']);
        $this->assertSame('支付名称', $items[1]['label']);
        $this->assertSame('Stripe', $items[1]['value']);
        $this->assertSame('安全状态', $items[7]['label']);
        $this->assertStringContainsString('已脱敏', $items[7]['value']);
        $this->assertSame('商户 KEY', $items[10]['label']);
        $this->assertStringContainsString('已配置', $items[10]['value']);
    }

    public function test_order_page_service_builds_table_and_detail_items()
    {
        $order = new Order();
        $order->forceFill([
            'id' => 350,
            'order_sn' => 'XIGUA-ORDER-350',
            'title' => '订单标题',
            'type' => Order::AUTOMATIC_DELIVERY,
            'email' => 'xigua@example.com',
            'goods_price' => 79,
            'buy_amount' => 1,
            'total_price' => 79,
            'coupon_discount_price' => 10,
            'wholesale_discount_price' => 0,
            'actual_price' => 69,
            'buy_ip' => '127.0.0.1',
            'search_pwd' => 'search-me',
            'trade_no' => 'trade-350',
            'status' => Order::STATUS_COMPLETED,
            'info' => "账号: demo\n密码: 123456",
            'created_at' => Carbon::parse('2026-04-10 14:00:00'),
            'updated_at' => Carbon::parse('2026-04-10 15:00:00'),
        ]);
        $order->setRelation('goods', (object) ['gd_name' => '订单商品']);
        $order->setRelation('coupon', (object) ['coupon' => 'XIGUA-350']);
        $order->setRelation('pay', (object) ['pay_name' => 'Stripe']);

        $service = $this->app->make(AdminShellOrderPageService::class);
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$order]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$order]), 1, 15));
        $filters = $service->buildFilters(['order_sn' => 'XIGUA', 'status' => Order::STATUS_COMPLETED, 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed', $order);
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$order]), 1, 15), ['order_sn' => 'XIGUA', 'scope' => '']);
        $showPage = $service->buildShowPageData($order, 'trashed');
        $items = $service->detailItems($order);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/order?order_sn=XIGUA&status=4&scope=trashed', 'GET'));

        $this->assertSame('订单管理', $header['title']);
        $this->assertSame('批量更新订单状态', $header['actions'][1]['label']);
        $this->assertSame('批量重置查询密码', $header['actions'][2]['label']);
        $this->assertSame('XIGUA', $requestFilters['order_sn']);
        $this->assertSame('订单状态', $filters['fields'][2]['label']);
        $this->assertSame('订单详情', $showHeader['title']);
        $this->assertStringContainsString('基础：XIGUA-ORDER-350 / 已完成 / 自动发货', $showHeader['meta']);
        $this->assertStringContainsString('交易：订单商品 / Stripe', $showHeader['meta']);
        $this->assertStringContainsString('金额：69 / 交易号 trade-350', $showHeader['meta']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('订单管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('订单详情 - 后台壳样板', $showPage->title);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('编辑订单', $showHeader['actions'][1]['label']);
        $this->assertSame('订单号', $table['headers'][1]);
        $this->assertStringContainsString('XIGUA-ORDER-350', $table['rows'][0][1]);
        $this->assertStringContainsString('编辑订单', $table['rows'][0][10]);
        $this->assertSame('当前条件下没有订单记录。', $table['empty_title']);
        $this->assertSame('基础信息', $items[0]['label']);
        $this->assertStringContainsString('订单号：XIGUA-ORDER-350', $items[0]['value']);
        $this->assertSame('商品与支付', $items[1]['label']);
        $this->assertStringContainsString('支付通道：Stripe', $items[1]['value']);
        $this->assertSame('金额与履约', $items[2]['label']);
        $this->assertStringContainsString('优惠码：XIGUA-350', $items[2]['value']);
        $this->assertSame('维护信息', $items[3]['label']);
        $this->assertStringContainsString('查询密码：search-me', $items[3]['value']);
        $this->assertSame('订单附加信息', $items[4]['label']);
        $this->assertStringContainsString('账号: demo', $items[4]['value']);
    }

    public function test_admin_shell_page_services_share_common_base_class()
    {
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellGoodsGroupPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellGoodsPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellOrderPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellEmailTemplatePageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellPayPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellCouponPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellCarmisPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellSystemSettingPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellEmailTestPageService::class));
    }

    public function test_coupon_page_service_builds_table_and_detail_items()
    {
        $coupon = new Coupon();
        $coupon->forceFill([
            'id' => 404,
            'discount' => 8.8,
            'coupon' => 'XIGUA-404',
            'ret' => 2,
            'is_use' => Coupon::STATUS_UNUSED,
            'is_open' => 1,
            'created_at' => Carbon::parse('2026-04-10 16:00:00'),
            'updated_at' => Carbon::parse('2026-04-10 17:00:00'),
        ]);
        $coupon->setRelation('goods', collect([
            (object) ['gd_name' => '西瓜会员'],
        ]));

        $service = $this->app->make(AdminShellCouponPageService::class);
        $this->assertInstanceOf(AdminShellPageServiceInterface::class, $service);
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$coupon]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$coupon]), 1, 15), ['coupon' => 'XIGUA', 'scope' => '']);
        $filters = $service->buildFilters(['coupon' => 'XIGUA', 'goods_id' => 404, 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed');
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$coupon]), 1, 15), ['coupon' => 'XIGUA', 'scope' => '']);
        $showPage = $service->buildShowPageData($coupon, 'trashed');
        $items = $service->detailItems($coupon);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/coupon?coupon=XIGUA&goods_id=404&scope=trashed', 'GET'));

        $this->assertSame('优惠码管理', $header['title']);
        $this->assertSame('导出优惠码文本', $header['actions'][1]['label']);
        $this->assertSame('导出优惠码 CSV', $header['actions'][2]['label']);
        $this->assertSame('批量启停优惠码', $header['actions'][3]['label']);
        $this->assertSame('XIGUA', $requestFilters['coupon']);
        $this->assertSame('商品 ID', $filters['fields'][2]['label']);
        $this->assertSame('优惠码详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('优惠码管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('优惠码详情 - 后台壳样板', $showPage->title);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('优惠码', $table['headers'][1]);
        $this->assertStringContainsString('XIGUA-404', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有优惠码记录。', $table['empty_title']);
        $this->assertSame('关联商品', $items[6]['label']);
        $this->assertSame('西瓜会员', $items[6]['value']);
    }

    public function test_carmis_page_service_builds_table_and_detail_items()
    {
        $carmi = new Carmis();
        $carmi->forceFill([
            'id' => 505,
            'goods_id' => 505,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => 'CARD-505-XYZ',
            'created_at' => Carbon::parse('2026-04-10 18:00:00'),
            'updated_at' => Carbon::parse('2026-04-10 19:00:00'),
        ]);
        $carmi->setRelation('goods', (object) ['gd_name' => '自动发货商品']);

        $service = $this->app->make(AdminShellCarmisPageService::class);
        $this->assertInstanceOf(AdminShellPageServiceInterface::class, $service);
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$carmi]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$carmi]), 1, 15), ['status' => Carmis::STATUS_UNSOLD]);
        $filters = $service->buildFilters(['goods_id' => 505, 'status' => Carmis::STATUS_UNSOLD, 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed');
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$carmi]), 1, 15), ['goods_id' => 505, 'scope' => '']);
        $showPage = $service->buildShowPageData($carmi, 'trashed');
        $items = $service->detailItems($carmi);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/carmis?goods_id=505&status=1&scope=trashed', 'GET'));

        $this->assertSame('卡密管理', $header['title']);
        $this->assertSame('505', $requestFilters['goods_id']);
        $this->assertSame('状态', $filters['fields'][2]['label']);
        $this->assertSame('卡密详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('卡密管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('卡密详情 - 后台壳样板', $showPage->title);
        $this->assertSame('导入卡密', $header['actions'][2]['label']);
        $this->assertSame('批量设置循环使用', $header['actions'][3]['label']);
        $this->assertSame('导出 CSV', $header['actions'][4]['label']);
        $this->assertSame('导出当前筛选', $header['actions'][5]['label']);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('关联商品', $table['headers'][1]);
        $this->assertStringContainsString('自动发货商品', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有卡密记录。', $table['empty_title']);
        $this->assertSame('卡密内容', $items[4]['label']);
        $this->assertSame('CARD-505-XYZ', $items[4]['value']);
    }

    public function test_system_setting_page_service_builds_table_and_detail_items()
    {
        $service = $this->app->make(AdminShellSystemSettingPageService::class);
        $this->assertInstanceOf(AdminShellPageServiceInterface::class, $service);

        $sections = $service->paginate(['section' => '邮件']);
        $header = $service->buildHeader($sections);
        $filters = $service->buildFilters(['section' => '邮件']);
        $showHeader = $service->buildShowHeader();
        $section = $service->find(4);
        $indexPage = $service->buildIndexPageData($sections, ['section' => '邮件']);
        $showPage = $service->buildShowPageData($section);
        $items = $service->detailItems($section);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/system-setting', 'GET', ['section' => '邮件']));

        $this->assertSame('系统设置概览', $header['title']);
        $this->assertSame('编辑订单行为配置', $header['actions'][4]['label']);
        $this->assertSame('邮件', $requestFilters['section']);
        $this->assertSame('分组关键字', $filters['fields'][0]['label']);
        $this->assertSame('系统设置详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('系统设置概览 - 后台壳样板', $indexPage->title);
        $this->assertSame('系统设置详情 - 后台壳样板', $showPage->title);
        $this->assertSame('配置分组', $service->buildTable($sections)['headers'][1]);
        $this->assertSame('当前条件下没有系统设置分组。', $service->buildTable($sections)['empty_title']);
        $this->assertSame('邮件驱动', $items[0]['label']);
    }

    public function test_email_test_page_service_builds_table_and_detail_items()
    {
        $service = $this->app->make(AdminShellEmailTestPageService::class);
        $this->assertInstanceOf(AdminShellPageServiceInterface::class, $service);

        $records = $service->paginate(['keyword' => '配置']);
        $header = $service->buildHeader($records);
        $filters = $service->buildFilters(['keyword' => '配置']);
        $showHeader = $service->buildShowHeader();
        $record = $service->find(2);
        $indexPage = $service->buildIndexPageData($records, ['keyword' => '配置']);
        $showPage = $service->buildShowPageData($record);
        $items = $service->detailItems($record);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/email-test', 'GET', ['keyword' => '配置']));

        $this->assertSame('邮件测试概览', $header['title']);
        $this->assertSame('配置', $requestFilters['keyword']);
        $this->assertSame('关键字', $filters['fields'][0]['label']);
        $this->assertSame('邮件测试详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('邮件测试概览 - 后台壳样板', $indexPage->title);
        $this->assertSame('邮件测试详情 - 后台壳样板', $showPage->title);
        $this->assertSame('页面分组', $service->buildTable($records)['headers'][1]);
        $this->assertSame('当前条件下没有邮件测试页面分组。', $service->buildTable($records)['empty_title']);
        $this->assertSame('邮件驱动', $items[0]['label']);
    }
}
