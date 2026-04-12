<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellPayControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('pays')->whereIn('id', [93001, 93002, 93003, 93004, 93005, 93006])->delete();
        DB::table('pays')->whereIn('pay_check', ['stripe', 'paypal', 'wechat-shell', 'alipay-shell'])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_pay_page(): void
    {
        DB::table('pays')->insert([
            'id' => 93001,
            'pay_name' => 'Stripe 样板',
            'merchant_id' => 'merchant-id',
            'merchant_key' => 'merchant-key',
            'merchant_pem' => 'merchant-pem',
            'pay_check' => 'stripe',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/stripe',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay');

        $response->assertOk();
        $response->assertSee('支付通道管理');
        $response->assertSee('Stripe 样板');
    }

    public function test_show_renders_pay_detail_page(): void
    {
        DB::table('pays')->insert([
            'id' => 93002,
            'pay_name' => 'PayPal 样板',
            'merchant_id' => 'paypal-id',
            'merchant_key' => 'paypal-key',
            'merchant_pem' => 'paypal-pem',
            'pay_check' => 'paypal',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/paypal',
            'pay_method' => 2,
            'is_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/93002');

        $response->assertOk();
        $response->assertSee('支付通道详情');
        $response->assertSee('PayPal 样板');
        $response->assertSee('/pay/paypal');
    }

    public function test_create_page_renders_pay_action_form(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/create');

        $response->assertOk();
        $response->assertSee('新建支付通道');
        $response->assertSee('支付配置分组');
        $response->assertSee('商户与密钥');
    }

    public function test_create_page_can_copy_existing_pay_channel(): void
    {
        DB::table('pays')->insert([
            'id' => 93005,
            'pay_name' => '复制样板',
            'merchant_id' => 'copy-merchant',
            'merchant_key' => 'copy-key',
            'merchant_pem' => 'copy-pem',
            'pay_check' => 'copy-shell',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/copy-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/create?copy=93005');

        $response->assertOk();
        $response->assertSee('复制支付通道');
        $response->assertSee('正在复制支付通道');
        $response->assertSee('复制样板（副本）');
        $response->assertSee('copy-merchant');
        $response->assertSee('/pay/copy-shell');
        $response->assertDontSee('copy-key');
        $response->assertDontSee('copy-pem');
    }

    public function test_copied_pay_channel_can_be_saved_as_new_record(): void
    {
        DB::table('pays')->insert([
            'id' => 93005,
            'pay_name' => '复制样板',
            'merchant_id' => 'copy-merchant',
            'merchant_key' => 'copy-key',
            'merchant_pem' => 'copy-pem',
            'pay_check' => 'copy-shell',
            'pay_client' => 2,
            'pay_handleroute' => '/pay/copy-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/create?copy=93005', [
                'pay_name' => '复制样板（副本）',
                'merchant_id' => 'copy-merchant',
                'merchant_key' => 'new-copy-key',
                'merchant_pem' => 'new-copy-pem',
                'pay_check' => 'copy-shell-clone',
                'pay_client' => 2,
                'pay_method' => 1,
                'pay_handleroute' => '/pay/copy-shell-clone',
                'is_open' => '1',
            ]);

        $record = DB::table('pays')->where('pay_check', 'copy-shell-clone')->first();
        $this->assertNotNull($record);
        $response->assertRedirect('/admin/v2/pay/'.$record->id.'/edit');
        $response->assertSessionHas('status', '支付通道已创建');
        $this->assertSame('复制样板（副本）', $record->pay_name);
        $this->assertSame('copy-merchant', $record->merchant_id);
        $this->assertSame('new-copy-key', $record->merchant_key);
        $this->assertSame('new-copy-pem', $record->merchant_pem);
        $this->assertSame('/pay/copy-shell-clone', $record->pay_handleroute);
        $this->assertSame(2, $record->pay_client);
        $this->assertSame(1, $record->pay_method);
        $this->assertSame(1, $record->is_open);
    }

    public function test_create_page_can_store_pay_channel(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/create', [
                'pay_name' => '微信样板',
                'merchant_id' => 'wechat-merchant',
                'merchant_key' => 'merchant-key',
                'merchant_pem' => 'merchant-pem',
                'pay_check' => 'wechat-shell',
                'pay_client' => 1,
                'pay_method' => 2,
                'pay_handleroute' => '/pay/wechat-shell',
                'is_open' => '1',
            ]);

        $record = DB::table('pays')->where('pay_check', 'wechat-shell')->first();
        $this->assertNotNull($record);
        $response->assertRedirect('/admin/v2/pay/'.$record->id.'/edit');
        $response->assertSessionHas('status', '支付通道已创建');
    }

    public function test_edit_page_renders_pay_action_form(): void
    {
        DB::table('pays')->insert([
            'id' => 93003,
            'pay_name' => '支付宝样板',
            'merchant_id' => 'ali-id',
            'merchant_key' => 'ali-key',
            'merchant_pem' => 'ali-pem',
            'pay_check' => 'alipay-shell',
            'pay_client' => 3,
            'pay_handleroute' => '/pay/alipay-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/93003/edit');

        $response->assertOk();
        $response->assertSee('编辑支付通道');
        $response->assertSee('留空保持现有值');
        $response->assertSee('商户与密钥');
        $response->assertDontSee('ali-key');
        $response->assertDontSee('ali-pem');
    }

    public function test_index_and_show_pages_include_copy_action(): void
    {
        DB::table('pays')->insert([
            'id' => 93006,
            'pay_name' => '复制入口样板',
            'merchant_id' => 'copy-entry-id',
            'merchant_key' => 'copy-entry-key',
            'merchant_pem' => 'copy-entry-pem',
            'pay_check' => 'copy-entry',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/copy-entry',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay');
        $response->assertOk();
        $response->assertSee('复制通道');

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/pay/93006');
        $response->assertOk();
        $response->assertSee('复制通道');
    }

    public function test_edit_page_can_update_pay_channel(): void
    {
        DB::table('pays')->insert([
            'id' => 93003,
            'pay_name' => '支付宝样板',
            'merchant_id' => 'ali-id',
            'merchant_key' => 'ali-key',
            'merchant_pem' => 'ali-pem',
            'pay_check' => 'alipay-shell',
            'pay_client' => 3,
            'pay_handleroute' => '/pay/alipay-shell',
            'pay_method' => 1,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/93003/edit', [
                'pay_name' => '支付宝样板已更新',
                'merchant_id' => 'ali-id-updated',
                'merchant_key' => 'ali-key-updated',
                'merchant_pem' => 'ali-pem-updated',
                'pay_check' => 'alipay-shell',
                'pay_client' => 2,
                'pay_method' => 2,
                'pay_handleroute' => '/pay/alipay-shell-updated',
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/pay/93003/edit');
        $response->assertSessionHas('status', '支付通道已保存');

        $record = DB::table('pays')->where('id', 93003)->first();
        $this->assertSame('支付宝样板已更新', $record->pay_name);
        $this->assertSame('ali-id-updated', $record->merchant_id);
        $this->assertSame('ali-key-updated', $record->merchant_key);
        $this->assertSame('ali-pem-updated', $record->merchant_pem);
        $this->assertSame('/pay/alipay-shell-updated', $record->pay_handleroute);
        $this->assertSame(2, $record->pay_client);
        $this->assertSame(2, $record->pay_method);
        $this->assertSame(0, $record->is_open);
    }

    public function test_edit_page_keeps_existing_secrets_when_left_blank(): void
    {
        DB::table('pays')->insert([
            'id' => 93004,
            'pay_name' => '微信样板',
            'merchant_id' => 'wechat-id',
            'merchant_key' => 'wechat-key',
            'merchant_pem' => 'wechat-pem',
            'pay_check' => 'wechat-shell-blank',
            'pay_client' => 1,
            'pay_handleroute' => '/pay/wechat-shell',
            'pay_method' => 2,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/pay/93004/edit', [
                'pay_name' => '微信样板已更新',
                'merchant_id' => 'wechat-id-updated',
                'merchant_key' => '',
                'merchant_pem' => '',
                'pay_check' => 'wechat-shell-blank',
                'pay_client' => 2,
                'pay_method' => 1,
                'pay_handleroute' => '/pay/wechat-shell-updated',
                'is_open' => '0',
            ]);

        $response->assertRedirect('/admin/v2/pay/93004/edit');
        $response->assertSessionHas('status', '支付通道已保存');

        $record = DB::table('pays')->where('id', 93004)->first();
        $this->assertSame('微信样板已更新', $record->pay_name);
        $this->assertSame('wechat-id-updated', $record->merchant_id);
        $this->assertSame('wechat-key', $record->merchant_key);
        $this->assertSame('wechat-pem', $record->merchant_pem);
        $this->assertSame('/pay/wechat-shell-updated', $record->pay_handleroute);
        $this->assertSame(2, $record->pay_client);
        $this->assertSame(1, $record->pay_method);
        $this->assertSame(0, $record->is_open);
    }

    private function makeAdmin(): Administrator
    {
        DB::table('admin_users')->updateOrInsert(
            ['username' => 'admin-shell-tester'],
            [
                'password' => bcrypt('secret123'),
                'name' => 'Admin Shell Tester',
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return Administrator::query()->where('username', 'admin-shell-tester')->firstOrFail();
    }
}
