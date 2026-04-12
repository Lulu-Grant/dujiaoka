<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\BaseModel;
use App\Service\SystemSettingService;
use Illuminate\Http\Request;

class SystemSettingActionController extends Controller
{
    /**
     * @var \App\Service\SystemSettingService
     */
    private $systemSettingService;

    public function __construct(SystemSettingService $systemSettingService)
    {
        $this->systemSettingService = $systemSettingService;
    }

    public function editBase()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-base', [
            'title' => '编辑基础站点配置 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑基础站点配置',
                'description' => '这是后台壳中的第二张操作型配置页面样板。当前先承接基础站点配置，验证后台壳对真实配置保存的能力。',
                'meta' => '当前为过渡样板，后续可继续扩展通知配置与邮件配置编辑',
                'actions' => [
                    ['label' => '返回系统设置概览', 'href' => admin_url('v2/system-setting')],
                    ['label' => '进入旧版功能页', 'href' => admin_url('system-setting'), 'variant' => 'primary'],
                ],
            ],
            'formAction' => admin_url('v2/system-setting/base'),
            'defaults' => [
                'title' => $settings['title'] ?? '',
                'text_logo' => $settings['text_logo'] ?? '',
                'template' => $settings['template'] ?? 'avatar',
                'language' => $settings['language'] ?? 'zh_CN',
                'manage_email' => $settings['manage_email'] ?? '',
                'order_expire_time' => $settings['order_expire_time'] ?? 5,
                'keywords' => $settings['keywords'] ?? '',
                'description' => $settings['description'] ?? '',
            ],
            'templateOptions' => config('dujiaoka.templates', []),
            'languageOptions' => config('dujiaoka.language', []),
        ]);
    }

    public function editBranding()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-branding', [
            'title' => '编辑品牌与 Logo 配置 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑品牌与 Logo 配置',
                'description' => '这是后台壳中的品牌配置样板页。当前独立承接站点标题、文字 Logo 与图片 Logo 路径配置，便于后续继续扩展媒体资源管理。',
                'meta' => '当前先使用文本路径方式维护图片 Logo，后续若需要可再接入专门的媒体上传壳',
                'actions' => [
                    ['label' => '返回系统设置概览', 'href' => admin_url('v2/system-setting')],
                    ['label' => '进入旧版功能页', 'href' => admin_url('system-setting'), 'variant' => 'primary'],
                ],
            ],
            'formAction' => admin_url('v2/system-setting/branding'),
            'defaults' => [
                'title' => $settings['title'] ?? '',
                'text_logo' => $settings['text_logo'] ?? '',
                'img_logo' => $settings['img_logo'] ?? '',
                'template' => $settings['template'] ?? 'avatar',
                'language' => $settings['language'] ?? 'zh_CN',
            ],
            'templateOptions' => config('dujiaoka.templates', []),
            'languageOptions' => config('dujiaoka.language', []),
        ]);
    }

    public function updateBase(Request $request)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'text_logo' => ['nullable', 'string', 'max:255'],
            'template' => ['required', 'string'],
            'language' => ['required', 'string'],
            'manage_email' => ['nullable', 'email'],
            'order_expire_time' => ['required', 'integer', 'min:1', 'max:1440'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/base'))
            ->with('status', '基础站点配置已保存');
    }

    public function updateBranding(Request $request)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'text_logo' => ['nullable', 'string', 'max:255'],
            'img_logo' => ['nullable', 'string', 'max:1000'],
            'template' => ['required', 'string'],
            'language' => ['required', 'string'],
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/branding'))
            ->with('status', '品牌与 Logo 配置已保存');
    }

    public function editMail()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-mail', [
            'title' => '编辑邮件配置 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑邮件配置',
                'description' => '这是后台壳中的邮件配置编辑样板页。当前先承接 SMTP 相关字段，继续验证后台壳对真实配置保存的能力。',
                'meta' => '当前为过渡样板，后续可继续扩展通知与推送配置编辑',
                'actions' => [
                    ['label' => '返回系统设置概览', 'href' => admin_url('v2/system-setting')],
                    ['label' => '进入旧版功能页', 'href' => admin_url('system-setting'), 'variant' => 'primary'],
                ],
            ],
            'formAction' => admin_url('v2/system-setting/mail'),
            'defaults' => [
                'driver' => $settings['driver'] ?? 'smtp',
                'host' => $settings['host'] ?? '',
                'port' => $settings['port'] ?? 587,
                'username' => $settings['username'] ?? '',
                'password' => $settings['password'] ?? '',
                'encryption' => $settings['encryption'] ?? '',
                'from_address' => $settings['from_address'] ?? '',
                'from_name' => $settings['from_name'] ?? '独角数卡西瓜版',
            ],
        ]);
    }

    public function updateMail(Request $request)
    {
        $payload = $request->validate([
            'driver' => ['required', 'string', 'max:50'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'string', 'max:50'],
            'from_address' => ['nullable', 'email'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/mail'))
            ->with('status', '邮件配置已保存');
    }

    public function editPush()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-push', [
            'title' => '编辑通知推送配置 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑通知推送配置',
                'description' => '这是后台壳中的通知推送配置样板页。当前先承接订单通知与推送相关字段，继续验证后台壳对真实配置保存的能力。',
                'meta' => '当前为过渡样板，后续可继续补更细的通知分组和运行时诊断入口',
                'actions' => [
                    ['label' => '返回系统设置概览', 'href' => admin_url('v2/system-setting')],
                    ['label' => '进入旧版功能页', 'href' => admin_url('system-setting'), 'variant' => 'primary'],
                ],
            ],
            'formAction' => admin_url('v2/system-setting/push'),
            'defaults' => [
                'is_open_server_jiang' => (int) ($settings['is_open_server_jiang'] ?? BaseModel::STATUS_CLOSE),
                'server_jiang_token' => $settings['server_jiang_token'] ?? '',
                'is_open_telegram_push' => (int) ($settings['is_open_telegram_push'] ?? BaseModel::STATUS_CLOSE),
                'telegram_bot_token' => $settings['telegram_bot_token'] ?? '',
                'telegram_userid' => $settings['telegram_userid'] ?? '',
                'is_open_bark_push' => (int) ($settings['is_open_bark_push'] ?? BaseModel::STATUS_CLOSE),
                'is_open_bark_push_url' => (int) ($settings['is_open_bark_push_url'] ?? BaseModel::STATUS_CLOSE),
                'bark_server' => $settings['bark_server'] ?? '',
                'bark_token' => $settings['bark_token'] ?? '',
                'is_open_qywxbot_push' => (int) ($settings['is_open_qywxbot_push'] ?? BaseModel::STATUS_CLOSE),
                'qywxbot_key' => $settings['qywxbot_key'] ?? '',
            ],
        ]);
    }

    public function updatePush(Request $request)
    {
        $payload = $request->validate([
            'server_jiang_token' => ['nullable', 'string', 'max:255'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_userid' => ['nullable', 'string', 'max:255'],
            'bark_server' => ['nullable', 'string', 'max:255'],
            'bark_token' => ['nullable', 'string', 'max:255'],
            'qywxbot_key' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = array_merge($payload, [
            'is_open_server_jiang' => $request->boolean('is_open_server_jiang') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_telegram_push' => $request->boolean('is_open_telegram_push') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_bark_push' => $request->boolean('is_open_bark_push') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_bark_push_url' => $request->boolean('is_open_bark_push_url') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_qywxbot_push' => $request->boolean('is_open_qywxbot_push') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/push'))
            ->with('status', '通知推送配置已保存');
    }

    public function editExperience()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-experience', [
            'title' => '编辑站点体验配置 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑站点体验配置',
                'description' => '这是后台壳中的站点体验配置样板页。当前先承接前台搜索密码、验证码、翻译、防红、公告和页脚等低风险字段。',
                'meta' => '当前为过渡样板，后续可继续扩展公告、SEO 和模板资源等更多展示型配置',
                'actions' => [
                    ['label' => '返回系统设置概览', 'href' => admin_url('v2/system-setting')],
                    ['label' => '进入旧版功能页', 'href' => admin_url('system-setting'), 'variant' => 'primary'],
                ],
            ],
            'formAction' => admin_url('v2/system-setting/experience'),
            'defaults' => [
                'is_open_anti_red' => (int) ($settings['is_open_anti_red'] ?? BaseModel::STATUS_CLOSE),
                'is_open_img_code' => (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE),
                'is_open_search_pwd' => (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE),
                'is_open_google_translate' => (int) ($settings['is_open_google_translate'] ?? BaseModel::STATUS_CLOSE),
                'notice' => $settings['notice'] ?? '',
                'footer' => $settings['footer'] ?? '',
            ],
        ]);
    }

    public function updateExperience(Request $request)
    {
        $payload = $request->validate([
            'notice' => ['nullable', 'string', 'max:20000'],
            'footer' => ['nullable', 'string', 'max:20000'],
        ]);

        $payload = array_merge($payload, [
            'is_open_anti_red' => $request->boolean('is_open_anti_red') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_img_code' => $request->boolean('is_open_img_code') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_search_pwd' => $request->boolean('is_open_search_pwd') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_google_translate' => $request->boolean('is_open_google_translate') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/experience'))
            ->with('status', '站点体验配置已保存');
    }
}
