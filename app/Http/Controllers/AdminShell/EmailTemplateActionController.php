<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Emailtpl;
use App\Service\EmailTemplateActionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailTemplateActionController extends Controller
{
    /**
     * @var \App\Service\EmailTemplateActionService
     */
    private $emailTemplateActionService;

    public function __construct(EmailTemplateActionService $emailTemplateActionService)
    {
        $this->emailTemplateActionService = $emailTemplateActionService;
    }

    public function create()
    {
        $defaults = $this->emailTemplateActionService->createDefaults();
        $previewContext = $this->emailTemplateActionService->previewContext();

        return view('admin-shell.emailtpl.form', [
            'title' => '新建邮件模板 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '新建邮件模板',
                'description' => '这是后台壳中的标准业务编辑页样板。当前先承接邮件模板的新建动作，右侧会同步展示预览和占位符说明，方便直接检查 HTML 效果。',
                'meta' => '模板标识在创建后将作为稳定引用键，建议使用语义明确且长期稳定的命名；模板内容支持 {webname}、{order_id} 等占位符',
                'actions' => [
                    ['label' => '返回邮件模板概览', 'href' => admin_url('v2/emailtpl')],
                ],
            ],
            'formAction' => admin_url('v2/emailtpl/create'),
            'submitLabel' => '创建邮件模板',
            'isCreate' => true,
            'defaults' => $defaults,
            'previewHtml' => $this->emailTemplateActionService->renderPreview($defaults['tpl_content'], $previewContext),
            'previewContext' => $previewContext,
            'previewTokens' => $this->emailTemplateActionService->previewTokens(),
            'usageGuide' => $this->emailTemplateActionService->usageGuide(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'tpl_name' => ['required', 'string', 'max:255'],
            'tpl_token' => ['required', 'string', 'max:255', Rule::unique('emailtpls', 'tpl_token')],
            'tpl_content' => ['required', 'string'],
        ]);

        $template = $this->emailTemplateActionService->create($payload);

        return redirect(admin_url('v2/emailtpl/'.$template->id.'/edit'))
            ->with('status', '邮件模板已创建');
    }

    public function edit(int $id)
    {
        $template = Emailtpl::query()->findOrFail($id);
        $defaults = $this->emailTemplateActionService->editDefaults($template);
        $previewContext = $this->emailTemplateActionService->previewContext();

        return view('admin-shell.emailtpl.form', [
            'title' => '编辑邮件模板 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑邮件模板',
                'description' => '这是后台壳中的邮件模板编辑样板页。当前复用普通 Laravel 控制器和服务处理模板内容更新，右侧会同步展示实时预览，方便直接检查 HTML 排版。',
                'meta' => '模板标识创建后保持只读，避免影响已存在的邮件通知引用关系；模板内容支持 {webname}、{order_id}、{ord_info} 等占位符',
                'actions' => [
                    ['label' => '返回邮件模板概览', 'href' => admin_url('v2/emailtpl')],
                    ['label' => '查看详情', 'href' => admin_url('v2/emailtpl/'.$template->id), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/emailtpl/'.$template->id.'/edit'),
            'submitLabel' => '保存邮件模板',
            'isCreate' => false,
            'defaults' => $defaults,
            'template' => $template,
            'previewHtml' => $this->emailTemplateActionService->renderPreview($defaults['tpl_content'], $previewContext),
            'previewContext' => $previewContext,
            'previewTokens' => $this->emailTemplateActionService->previewTokens(),
            'usageGuide' => $this->emailTemplateActionService->usageGuide(),
        ]);
    }

    public function update(int $id, Request $request)
    {
        $template = Emailtpl::query()->findOrFail($id);

        $payload = $request->validate([
            'tpl_name' => ['required', 'string', 'max:255'],
            'tpl_content' => ['required', 'string'],
        ]);

        $this->emailTemplateActionService->update($template, $payload);

        return redirect(admin_url('v2/emailtpl/'.$template->id.'/edit'))
            ->with('status', '邮件模板已保存');
    }
}
