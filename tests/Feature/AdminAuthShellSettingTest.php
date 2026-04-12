<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAuthShellSettingTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('admin_users')->where('username', 'auth-shell-setting-tester')->delete();
        parent::tearDown();
    }

    public function test_setting_page_renders_shell_profile_form(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/auth/setting');

        $response->assertOk();
        $response->assertSee('账号设置');
        $response->assertSee('基础资料');
        $response->assertSee('密码更新');
    }

    public function test_admin_can_update_name_and_avatar_in_shell_setting_page(): void
    {
        Storage::fake('admin');
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from('/admin/auth/setting')
            ->put('/admin/auth/setting', [
                'name' => 'Updated Shell Admin',
                'avatar' => UploadedFile::fake()->image('avatar.png', 120, 120),
            ]);

        $response->assertRedirect('/admin/auth/setting');
        $response->assertSessionHas('status');

        $admin->refresh();
        $this->assertSame('Updated Shell Admin', $admin->name);
        $this->assertNotNull($admin->avatar);
        Storage::disk('admin')->assertExists($admin->avatar);
    }

    public function test_admin_can_update_password_with_correct_old_password(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from('/admin/auth/setting')
            ->put('/admin/auth/setting', [
                'name' => $admin->name,
                'old_password' => 'secret123',
                'password' => 'secret456',
                'password_confirmation' => 'secret456',
            ]);

        $response->assertRedirect('/admin/auth/setting');

        $admin->refresh();
        $this->assertTrue(Hash::check('secret456', $admin->password));
    }

    public function test_password_update_requires_correct_old_password(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from('/admin/auth/setting')
            ->put('/admin/auth/setting', [
                'name' => $admin->name,
                'old_password' => 'wrong-password',
                'password' => 'secret456',
                'password_confirmation' => 'secret456',
            ]);

        $response->assertRedirect('/admin/auth/setting');
        $response->assertSessionHasErrors('old_password');

        $admin->refresh();
        $this->assertTrue(Hash::check('secret123', $admin->password));
    }

    private function makeAdmin(): Administrator
    {
        DB::table('admin_users')->updateOrInsert(
            ['username' => 'auth-shell-setting-tester'],
            [
                'password' => bcrypt('secret123'),
                'name' => 'Auth Shell Setting Tester',
                'avatar' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return Administrator::query()->where('username', 'auth-shell-setting-tester')->firstOrFail();
    }
}
