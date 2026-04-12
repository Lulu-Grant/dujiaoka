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
        $response->assertSee('订单行为配置');
        $response->assertSee('邮件发送配置');
        $response->assertDontSee('进入旧版功能页');
        $response->assertDontSee('/admin/system-setting');
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
            ->get('/admin/v2/system-setting/4');

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

    public function test_branding_edit_page_renders_shell_action_form(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'title' => '独角数卡西瓜版',
            'text_logo' => '独角西瓜',
            'img_logo' => '/logo/xigua.png',
            'template' => 'avatar',
            'language' => 'zh_CN',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/branding');

        $response->assertOk();
        $response->assertSee('编辑品牌与 Logo 配置');
        $response->assertSee('/logo/xigua.png');
        $response->assertSee('独角西瓜');
    }

    public function test_branding_edit_page_can_save_settings(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/system-setting/branding', [
                'title' => '西瓜品牌标题',
                'text_logo' => '西瓜文字 Logo',
                'img_logo' => '/assets/xigua/logo.png',
                'template' => 'avatar',
                'language' => 'zh_CN',
            ]);

        $response->assertRedirect('/admin/v2/system-setting/branding');
        $response->assertSessionHas('status', '品牌与 Logo 配置已保存');

        $settings = app(SystemSettingService::class)->all();
        $this->assertSame('西瓜品牌标题', $settings['title']);
        $this->assertSame('西瓜文字 Logo', $settings['text_logo']);
        $this->assertSame('/assets/xigua/logo.png', $settings['img_logo']);
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

    public function test_order_renders_system_setting_detail_page(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'order_expire_time' => 15,
            'is_open_img_code' => 1,
            'is_open_search_pwd' => 0,
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/3');

        $response->assertOk();
        $response->assertSee('系统设置详情');
        $response->assertSee('订单过期时间');
        $response->assertSee('15');
    }

    public function test_order_edit_page_renders_shell_action_form(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'order_expire_time' => 12,
            'is_open_img_code' => 1,
            'is_open_search_pwd' => 0,
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/order');

        $response->assertOk();
        $response->assertSee('编辑订单行为配置');
        $response->assertSee('12');
    }

    public function test_order_edit_page_can_save_settings(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/system-setting/order', [
                'order_expire_time' => 20,
                'is_open_img_code' => '1',
                'is_open_search_pwd' => '1',
            ]);

        $response->assertRedirect('/admin/v2/system-setting/order');
        $response->assertSessionHas('status', '订单行为配置已保存');

        $settings = app(SystemSettingService::class)->all();
        $this->assertSame(20, $settings['order_expire_time']);
        $this->assertSame(1, $settings['is_open_img_code']);
        $this->assertSame(1, $settings['is_open_search_pwd']);
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

    public function test_experience_edit_page_renders_shell_action_form(): void
    {
        Cache::forever(SystemSettingService::CACHE_KEY, [
            'is_open_anti_red' => 1,
            'is_open_img_code' => 1,
            'notice' => '站点公告内容',
            'footer' => '<p>footer</p>',
        ]);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->get('/admin/v2/system-setting/experience');

        $response->assertOk();
        $response->assertSee('编辑站点体验配置');
        $response->assertSee('站点公告内容');
        $response->assertSee('&lt;p&gt;footer&lt;/p&gt;', false);
    }

    public function test_experience_edit_page_can_save_settings(): void
    {
        Cache::forget(SystemSettingService::CACHE_KEY);

        $response = $this->actingAs($this->makeAdmin(), 'admin')
            ->post('/admin/v2/system-setting/experience', [
                'is_open_anti_red' => '1',
                'is_open_img_code' => '1',
                'is_open_search_pwd' => '1',
                'is_open_google_translate' => '0',
                'notice' => '新的站点公告',
                'footer' => '<p>新的页脚</p>',
            ]);

        $response->assertRedirect('/admin/v2/system-setting/experience');
        $response->assertSessionHas('status', '站点体验配置已保存');

        $settings = app(SystemSettingService::class)->all();
        $this->assertSame(1, $settings['is_open_anti_red']);
        $this->assertSame(1, $settings['is_open_img_code']);
        $this->assertSame(1, $settings['is_open_search_pwd']);
        $this->assertSame(0, $settings['is_open_google_translate']);
        $this->assertSame('新的站点公告', $settings['notice']);
        $this->assertSame('<p>新的页脚</p>', $settings['footer']);
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
