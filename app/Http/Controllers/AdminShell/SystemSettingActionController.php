<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
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
}
