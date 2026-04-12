<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\EmailTestSendService;
use Illuminate\Http\Request;

class EmailTestActionController extends Controller
{
    /**
     * @var \App\Service\EmailTestSendService
     */
    private $emailTestSendService;

    public function __construct(EmailTestSendService $emailTestSendService)
    {
        $this->emailTestSendService = $emailTestSendService;
    }

    public function create()
    {
        return view('admin-shell.email-test.send', [
            'title' => '发送测试邮件 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '发送测试邮件',
                'description' => '这是后台壳中的首个操作型配置页面样板。当前使用普通 Laravel 控制器和表单处理测试邮件发送。',
                'meta' => '推荐先确认运行时邮件配置，再执行测试发送',
                'actions' => [
                    ['label' => '返回邮件测试概览', 'href' => admin_url('v2/email-test')],
                    ['label' => '进入旧版功能页', 'href' => admin_url('email-test'), 'variant' => 'primary'],
                ],
            ],
            'formAction' => admin_url('v2/email-test/send'),
            'defaults' => $this->emailTestSendService->defaultPayload(),
            'runtimeSummary' => $this->emailTestSendService->runtimeSummary(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'to' => ['required', 'email'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
        ]);

        $this->emailTestSendService->send($payload);

        return redirect(admin_url('v2/email-test/send'))
            ->with('status', admin_trans('email-test.labels.success'));
    }
}
