<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Pay;
use App\Service\PayActionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayActionController extends Controller
{
    /**
     * @var \App\Service\PayActionService
     */
    private $payActionService;

    public function __construct(PayActionService $payActionService)
    {
        $this->payActionService = $payActionService;
    }

    public function create()
    {
        return view('admin-shell.pay.form', [
            'title' => '新建支付通道 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '新建支付通道',
                'description' => '这是后台壳中的支付通道新建样板页。当前先承接支付标识、商户配置、支付场景和回调路由等核心字段。',
                'meta' => '新版本已退役的通道不会自动重新进入前台主路径，但后台壳仍保持对配置数据的可控编辑能力',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                ],
            ],
            'formAction' => admin_url('v2/pay/create'),
            'submitLabel' => '创建支付通道',
            'isCreate' => true,
            'defaults' => $this->payActionService->createDefaults(),
            'methodOptions' => Pay::getMethodMap(),
            'clientOptions' => Pay::getClientMap(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request, true);
        $pay = $this->payActionService->create($payload);

        return redirect(admin_url('v2/pay/'.$pay->id.'/edit'))
            ->with('status', '支付通道已创建');
    }

    public function edit(int $id)
    {
        $pay = Pay::query()->findOrFail($id);

        return view('admin-shell.pay.form', [
            'title' => '编辑支付通道 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑支付通道',
                'description' => '这是后台壳中的支付通道编辑样板页。当前复用普通 Laravel 控制器和服务处理支付通道核心配置更新。',
                'meta' => '支付标识保持只读，避免影响现有支付入口、回调和生命周期判断',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                    ['label' => '查看详情', 'href' => admin_url('v2/pay/'.$pay->id), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/pay/'.$pay->id.'/edit'),
            'submitLabel' => '保存支付通道',
            'isCreate' => false,
            'defaults' => $this->payActionService->editDefaults($pay),
            'methodOptions' => Pay::getMethodMap(),
            'clientOptions' => Pay::getClientMap(),
            'payModel' => $pay,
        ]);
    }

    public function update(int $id, Request $request)
    {
        $pay = Pay::query()->findOrFail($id);
        $payload = $this->validatePayload($request, false, $pay);
        $this->payActionService->update($pay, $payload);

        return redirect(admin_url('v2/pay/'.$pay->id.'/edit'))
            ->with('status', '支付通道已保存');
    }

    private function validatePayload(Request $request, bool $isCreate, ?Pay $pay = null): array
    {
        $payCheckRules = ['required', 'string', 'max:255'];

        if ($isCreate) {
            $payCheckRules[] = Rule::unique('pays', 'pay_check');
        } else {
            $payCheckRules[] = Rule::unique('pays', 'pay_check')->ignore($pay ? $pay->id : null);
        }

        $payload = $request->validate([
            'pay_name' => ['required', 'string', 'max:255'],
            'merchant_id' => ['required', 'string', 'max:255'],
            'merchant_key' => ['nullable', 'string'],
            'merchant_pem' => ['required', 'string'],
            'pay_check' => $payCheckRules,
            'pay_client' => ['required', 'integer'],
            'pay_method' => ['required', 'integer'],
            'pay_handleroute' => ['required', 'string', 'max:255'],
        ]);

        return array_merge($payload, [
            'pay_client' => (int) $payload['pay_client'],
            'pay_method' => (int) $payload['pay_method'],
            'is_open' => $request->boolean('is_open') ? Pay::STATUS_OPEN : Pay::STATUS_CLOSE,
        ]);
    }
}
