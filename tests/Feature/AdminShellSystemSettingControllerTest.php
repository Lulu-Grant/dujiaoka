<?php

namespace Tests\Feature;

use App\Service\SystemSettingService;
use Dcat\Admin\Models\Administrator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminShellSystemSettingControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);
        DB::table('admin_users')->where('username', 'admin-shell-tester')->delete();

        parent::tearDown();
    }

    public function test_index_renders_plain_admin_shell_system_setting_page(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'title' => '独角数卡西瓜版',
            'text_logo' => '独角数卡西瓜版',
            'template' => 'avatar',
            'language' => 'zh_CN',
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting');

        $response->assertOk();
        $response->assertSee('系统设置概览');
        $response->assertSee('基础站点配置');
        $response->assertSee('邮件发送配置');
        $response->assertSee('进入旧版功能页');
        $response->assertSee('/admin/system-setting');
    }

    public function test_show_renders_system_setting_detail_page(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'title' => '独角数卡西瓜版',
            'text_logo' => '独角数卡西瓜版',
            'template' => 'avatar',
            'language' => 'zh_CN',
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
            'from_address' => 'hello@example.com',
            'from_name' => '独角数卡西瓜版',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/3');

        $response->assertOk();
        $response->assertSee('系统设置详情');
        $response->assertSee('邮件驱动');
        $response->assertSee('smtp.example.com');
    }

    public function test_base_edit_page_renders_shell_action_form(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'title' => '独角数卡西瓜版',
            'text_logo' => '独角数卡西瓜版',
            'template' => 'avatar',
            'language' => 'zh_CN',
            'manage_email' => 'admin@example.com',
            'order_expire_time' => 15,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/base');

        $response->assertOk();
        $response->assertSee('编辑基础站点配置');
        $response->assertSee('独角数卡西瓜版');
    }

    public function test_base_edit_page_can_save_settings(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/system-setting/base', [
                'title' => '新的站点标题',
                'text_logo' => '新的文字 Logo',
                'template' => 'avatar',
                'language' => 'zh_CN',
                'manage_email' => 'owner@example.com',
                'order_expire_time' => 30,
                'keywords' => 'kw',
                'description' => 'desc',
            ]);

        $response->assertRedirect('/admin/v2/system-setting/base');
        $response->assertSessionHas('status', '基础站点配置已保存');

        $settings = app(SystemSettingService::class)->all();
        $this->assertSame('新的站点标题', $settings['title']);
        $this->assertSame('owner@example.com', $settings['manage_email']);
        $this->assertSame(30, $settings['order_expire_time']);
    }

    public function test_mail_edit_page_renders_shell_action_form(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 465,
            'from_address' => 'mailer@example.com',
            'from_name' => '独角数卡西瓜版',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/mail');

        $response->assertOk();
        $response->assertSee('编辑邮件配置');
        $response->assertSee('smtp.example.com');
    }

    public function test_mail_edit_page_can_save_settings(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/system-setting/mail', [
                'driver' => 'smtp',
                'host' => 'mail.example.com',
                'port' => 2525,
                'username' => 'mailer',
                'password' => 'secret123',
                'encryption' => 'tls',
                'from_address' => 'mailer@example.com',
                'from_name' => '邮件机器人',
            ]);

        $response->assertRedirect('/admin/v2/system-setting/mail');
        $response->assertSessionHas('status', '邮件配置已保存');

        $settings = app(SystemSettingService::class)->all();
        $this->assertSame('mail.example.com', $settings['host']);
        $this->assertSame(2525, $settings['port']);
        $this->assertSame('邮件机器人', $settings['from_name']);
    }

    public function test_push_edit_page_renders_shell_action_form(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'is_open_server_jiang' => 1,
            'server_jiang_token' => 'server-token',
            'is_open_telegram_push' => 1,
            'telegram_bot_token' => 'bot-token',
            'telegram_userid' => '10001',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/push');

        $response->assertOk();
        $response->assertSee('编辑通知推送配置');
        $response->assertSee('server-token');
        $response->assertSee('bot-token');
    }

    public function test_push_edit_page_can_save_settings(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/system-setting/push', [
                'is_open_server_jiang' => '1',
                'server_jiang_token' => 'server-token',
                'is_open_telegram_push' => '1',
                'telegram_bot_token' => 'bot-token',
                'telegram_userid' => '10001',
                'is_open_bark_push' => '1',
                'is_open_bark_push_url' => '1',
                'bark_server' => 'https://bark.example.com',
                'bark_token' => 'bark-token',
                'is_open_qywxbot_push' => '1',
                'qywxbot_key' => 'qywx-key',
            ]);

        $response->assertRedirect('/admin/v2/system-setting/push');
        $response->assertSessionHas('status', '通知推送配置已保存');

        $settings = app(SystemSettingService::class)->all();
        $this->assertSame(1, $settings['is_open_server_jiang']);
        $this->assertSame('server-token', $settings['server_jiang_token']);
        $this->assertSame(1, $settings['is_open_bark_push']);
        $this->assertSame('https://bark.example.com', $settings['bark_server']);
        $this->assertSame('qywx-key', $settings['qywxbot_key']);
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
