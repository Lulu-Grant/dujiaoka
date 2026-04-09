<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellPayControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('pays')->whereIn('id', [93001, 93002])->delete();
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
