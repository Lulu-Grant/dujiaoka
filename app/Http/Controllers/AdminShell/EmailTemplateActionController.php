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

    public function create(Request $request)
    {
        $copyTemplate = $this->resolveCopyTemplate($request);
        $defaults = $this->emailTemplateActionService->createDefaults($copyTemplate);
        $previewContext = $this->emailTemplateActionService->previewContext();

        if ($request->boolean('preview')) {
            return view('admin-shell.emailtpl.preview', $this->emailTemplateActionService->buildPreviewPageData($copyTemplate, true));
        }

        return view('admin-shell.emailtpl.form', [
            'title' => $copyTemplate ? '复制邮件模板 - 后台壳样板' : '新建邮件模板 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => $copyTemplate ? '复制邮件模板' : '新建邮件模板',
                'description' => $copyTemplate
                    ? '这是后台壳中的邮件模板复制样板页。当前会从现有模板预填标题和内容，方便在此基础上快速生成新模板。'
                    : '这是后台壳中的标准业务编辑页样板。当前先承接邮件模板的新建动作，右侧会同步展示预览和占位符说明，方便直接检查 HTML 效果。',
                'meta' => $copyTemplate
                    ? '模板内容会从现有模板复制过来，但模板标识会保持为空，避免覆盖原模板引用。'
                    : '模板标识在创建后将作为稳定引用键，建议使用语义明确且长期稳定的命名；模板内容支持 {webname}、{order_id} 等占位符',
                'actions' => [
                    ['label' => '返回邮件模板概览', 'href' => admin_url('v2/emailtpl')],
                ],
            ],
            'formAction' => admin_url('v2/emailtpl/create').($copyTemplate ? '?copy='.$copyTemplate->id : ''),
            'submitLabel' => $copyTemplate ? '复制并创建邮件模板' : '创建邮件模板',
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

    public function edit(int $id, Request $request)
    {
        $template = Emailtpl::query()->findOrFail($id);
        $defaults = $this->emailTemplateActionService->editDefaults($template);
        $previewContext = $this->emailTemplateActionService->previewContext();

        if ($request->boolean('preview')) {
            return view('admin-shell.emailtpl.preview', $this->emailTemplateActionService->buildPreviewPageData($template));
        }

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

    private function resolveCopyTemplate(Request $request): ?Emailtpl
    {
        $copyId = $request->query('copy');

        if (!$copyId) {
            return null;
        }

        return Emailtpl::query()->findOrFail((int) $copyId);
    }
}
