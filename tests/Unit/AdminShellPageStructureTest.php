<?php

namespace Tests\Unit;

use App\Models\Emailtpl;
use App\Models\GoodsGroup;
use App\Models\Coupon;
use App\Models\Carmis;
use App\Models\Pay;
use App\Service\AbstractAdminShellPageService;
use App\Service\AdminShellCarmisPageService;
use App\Service\AdminShellCouponPageService;
use App\Service\Contracts\AdminShellPageServiceInterface;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use App\Service\AdminShellEmailTemplatePageService;
use App\Service\AdminShellGoodsGroupPageService;
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
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$template]), 1, 15));
        $filters = $service->buildFilters(['tpl_name' => '发货', 'tpl_token' => 'deliver_notice']);
        $showHeader = $service->buildShowHeader();
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$template]), 1, 15), ['tpl_name' => '发货']);
        $showPage = $service->buildShowPageData($template);
        $items = $service->detailItems($template);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/emailtpl?tpl_name=test&tpl_token=deliver_notice', 'GET'));

        $this->assertSame('邮件模板管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
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
        $showHeader = $service->buildShowHeader('trashed');
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$pay]), 1, 15), ['pay_check' => 'stripe', 'scope' => '']);
        $showPage = $service->buildShowPageData($pay, 'trashed');
        $items = $service->detailItems($pay);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/pay?pay_check=stripe&pay_name=Stripe&scope=trashed', 'GET'));

        $this->assertSame('支付通道管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
        $this->assertSame('Stripe', $requestFilters['pay_name']);
        $this->assertSame('支付标识', $filters['fields'][1]['label']);
        $this->assertSame('支付通道详情', $showHeader['title']);
        $this->assertInstanceOf(AdminShellIndexPageData::class, $indexPage);
        $this->assertInstanceOf(AdminShellShowPageData::class, $showPage);
        $this->assertSame('支付通道管理 - 后台壳样板', $indexPage->title);
        $this->assertSame('支付通道详情 - 后台壳样板', $showPage->title);
        $this->assertSame('支付通道管理 - 后台壳样板', $indexPage->toViewData()['title']);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('支付名称', $table['headers'][1]);
        $this->assertStringContainsString('Stripe', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有支付通道记录。', $table['empty_title']);
        $this->assertSame('支付名称', $items[1]['label']);
        $this->assertSame('Stripe', $items[1]['value']);
    }

    public function test_admin_shell_page_services_share_common_base_class()
    {
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellGoodsGroupPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellEmailTemplatePageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellPayPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellCouponPageService::class));
        $this->assertInstanceOf(AbstractAdminShellPageService::class, $this->app->make(AdminShellCarmisPageService::class));
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
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$coupon]), 1, 15));
        $filters = $service->buildFilters(['coupon' => 'XIGUA', 'goods_id' => 404, 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed');
        $indexPage = $service->buildIndexPageData(new LengthAwarePaginator(collect([$coupon]), 1, 15), ['coupon' => 'XIGUA', 'scope' => '']);
        $showPage = $service->buildShowPageData($coupon, 'trashed');
        $items = $service->detailItems($coupon);
        $requestFilters = $service->extractFilters(Request::create('/admin/v2/coupon?coupon=XIGUA&goods_id=404&scope=trashed', 'GET'));

        $this->assertSame('优惠码管理', $header['title']);
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
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$carmi]), 1, 15));
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
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('关联商品', $table['headers'][1]);
        $this->assertStringContainsString('自动发货商品', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有卡密记录。', $table['empty_title']);
        $this->assertSame('卡密内容', $items[4]['label']);
        $this->assertSame('CARD-505-XYZ', $items[4]['value']);
    }
}
