<?php

namespace Tests\Unit;

use App\Models\Emailtpl;
use App\Models\GoodsGroup;
use App\Models\Pay;
use App\Service\AdminShellEmailTemplatePageService;
use App\Service\AdminShellGoodsGroupPageService;
use App\Service\AdminShellPayPageService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$group]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$group]), 1, 15));
        $filters = $service->buildFilters(['id' => 101, 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed');
        $items = $service->detailItems($group);

        $this->assertSame('商品分类管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
        $this->assertSame('范围', $filters['fields'][1]['label']);
        $this->assertSame('商品分类详情', $showHeader['title']);
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
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$template]), 1, 15)
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$template]), 1, 15));
        $filters = $service->buildFilters(['tpl_name' => '发货', 'tpl_token' => 'deliver_notice']);
        $showHeader = $service->buildShowHeader();
        $items = $service->detailItems($template);

        $this->assertSame('邮件模板管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
        $this->assertSame('邮件标题', $filters['fields'][1]['label']);
        $this->assertSame('邮件模板详情', $showHeader['title']);
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
        $table = $service->buildTable(
            new LengthAwarePaginator(collect([$pay]), 1, 15),
            ['scope' => '']
        );
        $header = $service->buildHeader(new LengthAwarePaginator(collect([$pay]), 1, 15));
        $filters = $service->buildFilters(['pay_check' => 'stripe', 'scope' => 'trashed']);
        $showHeader = $service->buildShowHeader('trashed');
        $items = $service->detailItems($pay);

        $this->assertSame('支付通道管理', $header['title']);
        $this->assertSame('迁移合同', $header['actions'][0]['label']);
        $this->assertSame('支付标识', $filters['fields'][1]['label']);
        $this->assertSame('支付通道详情', $showHeader['title']);
        $this->assertStringContainsString('?scope=trashed', $showHeader['actions'][0]['href']);
        $this->assertSame('支付名称', $table['headers'][1]);
        $this->assertStringContainsString('Stripe', $table['rows'][0][1]);
        $this->assertSame('当前条件下没有支付通道记录。', $table['empty_title']);
        $this->assertSame('支付名称', $items[1]['label']);
        $this->assertSame('Stripe', $items[1]['value']);
    }
}
