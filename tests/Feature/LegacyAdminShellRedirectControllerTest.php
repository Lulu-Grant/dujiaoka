<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyAdminShellRedirectControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('admin_role_users')->where('user_id', 1761)->delete();
        DB::table('admin_users')->where('username', 'admin-shell-redirect-tester')->delete();

        parent::tearDown();
    }

    public function test_goods_group_legacy_routes_redirect_to_admin_shell(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods-group?scope=trashed')
            ->assertRedirect('/admin/v2/goods-group?scope=trashed');

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods-group/create')
            ->assertRedirect('/admin/v2/goods-group/create');

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods-group/123')
            ->assertRedirect('/admin/v2/goods-group/123');

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods-group/123/edit')
            ->assertRedirect('/admin/v2/goods-group/123/edit');
    }

    public function test_business_resource_legacy_routes_redirect_to_admin_shell(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin/coupon')
            ->assertRedirect('/admin/v2/coupon');

        $this->actingAs($admin, 'admin')
            ->get('/admin/coupon/create')
            ->assertRedirect('/admin/v2/coupon/create');

        $this->actingAs($admin, 'admin')
            ->get('/admin/coupon/456')
            ->assertRedirect('/admin/v2/coupon/456');

        $this->actingAs($admin, 'admin')
            ->get('/admin/carmis')
            ->assertRedirect('/admin/v2/carmis');

        $this->actingAs($admin, 'admin')
            ->get('/admin/emailtpl')
            ->assertRedirect('/admin/v2/emailtpl');

        $this->actingAs($admin, 'admin')
            ->get('/admin/emailtpl/create')
            ->assertRedirect('/admin/v2/emailtpl/create');

        $this->actingAs($admin, 'admin')
            ->get('/admin/emailtpl/123')
            ->assertRedirect('/admin/v2/emailtpl/123');

        $this->actingAs($admin, 'admin')
            ->get('/admin/emailtpl/123/edit')
            ->assertRedirect('/admin/v2/emailtpl/123/edit');

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods')
            ->assertRedirect('/admin/v2/goods');

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods/create')
            ->assertRedirect('/admin/v2/goods/create');

        $this->actingAs($admin, 'admin')
            ->get('/admin/pay/create')
            ->assertRedirect('/admin/v2/pay/create');

        $this->actingAs($admin, 'admin')
            ->get('/admin/coupon/456/edit')
            ->assertRedirect('/admin/v2/coupon/456/edit');

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods/789')
            ->assertRedirect('/admin/v2/goods/789');

        $this->actingAs($admin, 'admin')
            ->get('/admin/goods/789/edit')
            ->assertRedirect('/admin/v2/goods/789/edit');

        $this->actingAs($admin, 'admin')
            ->get('/admin/carmis/789')
            ->assertRedirect('/admin/v2/carmis/789');

        $this->actingAs($admin, 'admin')
            ->get('/admin/carmis/789/edit')
            ->assertRedirect('/admin/v2/carmis/789/edit');

        $this->actingAs($admin, 'admin')
            ->get('/admin/carmis/import')
            ->assertRedirect('/admin/v2/carmis/import');

        $this->actingAs($admin, 'admin')
            ->get('/admin/order?status=4')
            ->assertRedirect('/admin/v2/order?status=4');

        $this->actingAs($admin, 'admin')
            ->get('/admin/order/321')
            ->assertRedirect('/admin/v2/order/321');

        $this->actingAs($admin, 'admin')
            ->get('/admin/order/321/edit')
            ->assertRedirect('/admin/v2/order/321/edit');
    }

    public function test_configuration_legacy_routes_redirect_to_admin_shell(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin/system-setting')
            ->assertRedirect('/admin/v2/system-setting');

        $this->actingAs($admin, 'admin')
            ->get('/admin/email-test')
            ->assertRedirect('/admin/v2/email-test');
    }

    public function test_import_carmis_legacy_route_redirects_to_shell_import_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin/import-carmis')
            ->assertRedirect('/admin/v2/carmis/import');
    }

    public function test_configuration_legacy_routes_preserve_query_strings(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin/system-setting?section=mail')
            ->assertRedirect('/admin/v2/system-setting?section=mail');

        $this->actingAs($admin, 'admin')
            ->get('/admin/email-test?keyword=config')
            ->assertRedirect('/admin/v2/email-test?keyword=config');
    }

    public function test_admin_root_redirects_to_admin_shell_dashboard(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertRedirect('/admin/v2/dashboard');
    }

    private function makeAdmin(): Administrator
    {
        DB::table('admin_users')->updateOrInsert(
            ['username' => 'admin-shell-redirect-tester'],
            [
                'id' => 1761,
                'password' => bcrypt('secret123'),
                'name' => 'Admin Shell Redirect Tester',
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('admin_role_users')->updateOrInsert(
            ['role_id' => 1, 'user_id' => 1761],
            []
        );

        return Administrator::query()->where('username', 'admin-shell-redirect-tester')->firstOrFail();
    }
}
