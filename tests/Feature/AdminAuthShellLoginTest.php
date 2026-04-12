<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAuthShellLoginTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('admin_users')->where('username', 'auth-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_login_page_renders_admin_shell_login_view(): void
    {
        $response = $this->get('/admin/auth/login');

        $response->assertOk();
        $response->assertSee('独角数卡西瓜版');
        $response->assertSee('进入后台控制中心');
        $response->assertSee('Admin Shell First');
    }

    public function test_admin_can_login_from_shell_login_page(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->post('/admin/auth/login', [
            'username' => $admin->username,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_invalid_login_shows_error_message(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->from('/admin/auth/login')->post('/admin/auth/login', [
            'username' => $admin->username,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/auth/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout_back_to_shell_login_page(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/auth/logout');

        $response->assertRedirect('/admin/auth/login');
        $this->assertGuest('admin');
    }

    private function makeAdmin(): Administrator
    {
        DB::table('admin_users')->updateOrInsert(
            ['username' => 'auth-shell-tester'],
            [
                'password' => bcrypt('secret123'),
                'name' => 'Auth Shell Tester',
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return Administrator::query()->where('username', 'auth-shell-tester')->firstOrFail();
    }
}
