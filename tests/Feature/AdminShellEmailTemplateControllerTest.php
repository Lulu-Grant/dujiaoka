<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellEmailTemplateControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('emailtpls')->whereIn('id', [92001, 92002])->delete();
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_email_template_page(): void
    {
        DB::table('emailtpls')->insert([
            'id' => 92001,
            'tpl_name' => '模板 A',
            'tpl_content' => '欢迎使用独角数卡西瓜版',
            'tpl_token' => 'shell-template-a',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/emailtpl');

        $response->assertOk();
        $response->assertSee('邮件模板管理');
        $response->assertSee('模板 A');
    }

    public function test_show_renders_email_template_detail_page(): void
    {
        DB::table('emailtpls')->insert([
            'id' => 92002,
            'tpl_name' => '模板 B',
            'tpl_content' => '这是一段模板内容',
            'tpl_token' => 'shell-template-b',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/emailtpl/92002');

        $response->assertOk();
        $response->assertSee('邮件模板详情');
        $response->assertSee('模板 B');
        $response->assertSee('shell-template-b');
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
