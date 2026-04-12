<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellEmailTemplateControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('emailtpls')->whereIn('id', [92001, 92002, 92003])->delete();
        DB::table('emailtpls')->whereIn('tpl_token', ['shell-template-a', 'shell-template-b', 'shell-template-c', 'shell-created-template'])->delete();
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
        $response->assertSee('占位符');
        $response->assertSee('预览样例模板');
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
        $response->assertSee('使用说明');
        $response->assertSee('编辑建议');
        $response->assertSee('编辑模板');
        $response->assertSee('预览模板');
    }

    public function test_create_page_renders_email_template_action_form(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/emailtpl/create');

        $response->assertOk();
        $response->assertSee('新建邮件模板');
        $response->assertSee('模板标识');
        $response->assertSee('邮件模板预览');
        $response->assertSee('使用说明');
        $response->assertSee('{webname}');
    }

    public function test_create_preview_page_renders_email_template_preview_page(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/emailtpl/create?preview=1');

        $response->assertOk();
        $response->assertSee('新建邮件模板预览');
        $response->assertSee('模板标题与内容预览');
        $response->assertSee('邮件模板预览');
        $response->assertSee('占位符参考');
        $response->assertSee('返回创建页');
    }

    public function test_create_page_can_store_email_template(): void
    {
        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/emailtpl/create', [
                'tpl_name' => '模板 C',
                'tpl_token' => 'shell-created-template',
                'tpl_content' => '模板内容 C',
            ]);

        $template = DB::table('emailtpls')->where('tpl_token', 'shell-created-template')->first();

        $this->assertNotNull($template);
        $response->assertRedirect('/admin/v2/emailtpl/'.$template->id.'/edit');
        $response->assertSessionHas('status', '邮件模板已创建');
    }

    public function test_edit_page_renders_email_template_action_form(): void
    {
        DB::table('emailtpls')->insert([
            'id' => 92003,
            'tpl_name' => '模板 C',
            'tpl_content' => '<p>订单号：{order_id}</p><p>站点：{webname}</p>',
            'tpl_token' => 'shell-template-c',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/emailtpl/92003/edit');

        $response->assertOk();
        $response->assertSee('编辑邮件模板');
        $response->assertSee('shell-template-c');
        $response->assertSee('邮件模板预览');
        $response->assertSee('XIGUA-20260412-0001');
        $response->assertSee('使用说明');
        $response->assertSee('占位符参考');
    }

    public function test_edit_preview_page_renders_email_template_preview_page(): void
    {
        DB::table('emailtpls')->insert([
            'id' => 92003,
            'tpl_name' => '模板 C',
            'tpl_content' => '<p>订单号：{order_id}</p><p>站点：{webname}</p>',
            'tpl_token' => 'shell-template-c',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/emailtpl/92003/edit?preview=1');

        $response->assertOk();
        $response->assertSee('邮件模板预览');
        $response->assertSee('模板标题与内容预览');
        $response->assertSee('模板 C');
        $response->assertSee('shell-template-c');
        $response->assertSee('XIGUA-20260412-0001');
        $response->assertSee('返回编辑页');
    }

    public function test_edit_page_can_update_email_template(): void
    {
        DB::table('emailtpls')->insert([
            'id' => 92003,
            'tpl_name' => '模板 C',
            'tpl_content' => '<p>订单号：{order_id}</p><p>站点：{webname}</p>',
            'tpl_token' => 'shell-template-c',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/emailtpl/92003/edit', [
                'tpl_name' => '模板 C 已更新',
                'tpl_content' => '更新后的模板内容',
            ]);

        $response->assertRedirect('/admin/v2/emailtpl/92003/edit');
        $response->assertSessionHas('status', '邮件模板已保存');

        $record = DB::table('emailtpls')->where('id', 92003)->first();
        $this->assertSame('模板 C 已更新', $record->tpl_name);
        $this->assertSame('更新后的模板内容', $record->tpl_content);
        $this->assertSame('shell-template-c', $record->tpl_token);
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
