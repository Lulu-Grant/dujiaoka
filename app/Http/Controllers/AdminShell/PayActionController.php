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

    public function create(Request $request)
    {
        $sourcePay = $this->resolveCopySource($request);

        return view('admin-shell.pay.form', [
            'title' => $sourcePay ? '复制支付通道 - 后台壳样板' : '新建支付通道 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => $sourcePay ? '复制支付通道' : '新建支付通道',
                'description' => $sourcePay
                    ? '这是后台壳中的支付通道复制样板页。当前会预填原通道的非敏感配置，敏感字段需要重新确认后再保存。'
                    : '这是后台壳中的支付通道新建样板页。当前先承接支付标识、商户配置、支付场景和回调路由等核心字段。',
                'meta' => $sourcePay
                    ? '复制动作只会带入非敏感配置，商户 KEY 与商户 PEM 需要重新填写。'
                    : '新版本已退役的通道不会自动重新进入前台主路径，但后台壳仍保持对配置数据的可控编辑能力',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                ],
            ],
            'formAction' => admin_url('v2/pay/create'),
            'submitLabel' => '创建支付通道',
            'isCreate' => true,
            'sourcePay' => $sourcePay,
            'context' => $this->payActionService->createContext($sourcePay),
            'sections' => $this->payActionService->createSections($sourcePay),
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
            'context' => $this->payActionService->editContext($pay),
            'sections' => $this->payActionService->editSections($pay),
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

    public function editBatchStatus(Request $request)
    {
        $payIds = $this->payActionService->parsePayIds((string) $request->query('ids', ''));
        $defaults = $this->payActionService->batchStatusDefaults($payIds);

        return view('admin-shell.pay.batch-status', [
            'title' => '批量启停支付通道 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量启停支付通道',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接支付通道启用状态切换，不触碰商户密钥、回调路由和其他配置。',
                'meta' => '适合一口气处理活动下架、灰度启用和临时停用等场景。输入支付通道 ID 即可预览并执行。',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                    ['label' => '新建支付通道', 'href' => admin_url('v2/pay/create'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/pay/batch-status'),
            'submitLabel' => '执行批量状态更新',
            'defaults' => $defaults,
            'context' => $this->payActionService->batchStatusContext($payIds),
        ]);
    }

    public function editBatchClient(Request $request)
    {
        $payIds = $this->payActionService->parsePayIds((string) $request->query('ids', ''));
        $defaults = $this->payActionService->batchClientDefaults($payIds);

        return view('admin-shell.pay.batch-client', [
            'title' => '批量切换支付场景 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量切换支付场景',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接支付场景切换，不触碰商户密钥、支付标识、回调路由和生命周期。',
                'meta' => '适合统一把一批通道切到 PC、H5 或通用场景，便于活动切流和入口整顿。提交后只更新支付场景字段。',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                    ['label' => '批量启停通道', 'href' => admin_url('v2/pay/batch-status'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/pay/batch-client'),
            'submitLabel' => '执行批量支付场景切换',
            'defaults' => $defaults,
            'context' => $this->payActionService->batchClientContext($payIds),
            'clientOptions' => Pay::getClientMap(),
        ]);
    }

    public function editBatchMethod(Request $request)
    {
        $payIds = $this->payActionService->parsePayIds((string) $request->query('ids', ''));
        $defaults = $this->payActionService->batchMethodDefaults($payIds);

        return view('admin-shell.pay.batch-method', [
            'title' => '批量切换支付方式 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量切换支付方式',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接支付方式切换，不触碰商户密钥、支付标识、回调路由和生命周期。',
                'meta' => '适合统一把一批通道切到跳转或扫码方式，便于活动切流和配置收口。提交后只更新支付方式字段。',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                    ['label' => '批量切换场景', 'href' => admin_url('v2/pay/batch-client'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/pay/batch-method'),
            'submitLabel' => '执行批量支付方式切换',
            'defaults' => $defaults,
            'context' => $this->payActionService->batchClientContext($payIds),
            'methodOptions' => Pay::getMethodMap(),
        ]);
    }

    public function editBatchName(Request $request)
    {
        $payIds = $this->payActionService->parsePayIds((string) $request->query('ids', ''));
        $defaults = $this->payActionService->batchNameDefaults($payIds);

        return view('admin-shell.pay.batch-name', [
            'title' => '批量设置支付名称 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置支付名称',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接支付通道展示名称调整，不触碰支付标识、商户密钥、场景、方式和回调路由。',
                'meta' => '适合统一整理通道展示名、活动命名和渠道别名。提交后只更新支付名称字段。',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                    ['label' => '批量切换方式', 'href' => admin_url('v2/pay/batch-method'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/pay/batch-name'),
            'submitLabel' => '执行批量支付名称更新',
            'defaults' => $defaults,
            'context' => $this->payActionService->batchClientContext($payIds),
        ]);
    }

    public function editBatchNamePrefix(Request $request)
    {
        $payIds = $this->payActionService->parsePayIds((string) $request->query('ids', ''));
        $defaults = $this->payActionService->batchNamePrefixDefaults($payIds);

        return view('admin-shell.pay.batch-name-prefix', [
            'title' => '批量添加支付名称前缀 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量添加支付名称前缀',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接支付通道展示名称前缀调整，不触碰支付标识、商户密钥、场景、方式和回调路由。',
                'meta' => '适合活动期统一标记通道、区分渠道批次或加运营标签。提交后会把目标前缀追加到当前支付名称前面。',
                'actions' => [
                    ['label' => '返回支付通道概览', 'href' => admin_url('v2/pay')],
                    ['label' => '批量设置名称', 'href' => admin_url('v2/pay/batch-name'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/pay/batch-name-prefix'),
            'submitLabel' => '执行批量前缀更新',
            'defaults' => $defaults,
            'context' => $this->payActionService->batchClientContext($payIds),
        ]);
    }

    public function updateBatchStatus(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'is_open' => ['required', Rule::in([Pay::STATUS_OPEN, Pay::STATUS_CLOSE, '0', '1'])],
        ]);

        $payIds = $this->payActionService->parsePayIds($validated['ids_text']);
        if (empty($payIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的支付通道 ID。'])
                ->withInput();
        }

        $affected = $this->payActionService->updateOpenStatus($payIds, (int) $validated['is_open']);
        $statusLabel = (int) $validated['is_open'] === Pay::STATUS_OPEN ? '启用' : '停用';

        return redirect(admin_url('v2/pay/batch-status').'?ids='.implode(',', $payIds))
            ->with('status', '已批量'.$statusLabel.' '.$affected.' 个支付通道');
    }

    public function updateBatchClient(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'pay_client' => ['required', Rule::in(array_keys(Pay::getClientMap()))],
        ]);

        $payIds = $this->payActionService->parsePayIds($validated['ids_text']);
        if (empty($payIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的支付通道 ID。'])
                ->withInput();
        }

        $targetClient = (int) $validated['pay_client'];
        $affected = $this->payActionService->updateClient($payIds, $targetClient);
        $clientLabel = Pay::getClientMap()[$targetClient] ?? (string) $targetClient;

        return redirect(admin_url('v2/pay/batch-client').'?ids='.implode(',', $payIds))
            ->with('status', '已批量切换 '.$affected.' 个支付通道到 '.$clientLabel.' 场景');
    }

    public function updateBatchMethod(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'pay_method' => ['required', Rule::in(array_keys(Pay::getMethodMap()))],
        ]);

        $payIds = $this->payActionService->parsePayIds($validated['ids_text']);
        if (empty($payIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的支付通道 ID。'])
                ->withInput();
        }

        $targetMethod = (int) $validated['pay_method'];
        $affected = $this->payActionService->updateMethod($payIds, $targetMethod);
        $methodLabel = Pay::getMethodMap()[$targetMethod] ?? (string) $targetMethod;

        return redirect(admin_url('v2/pay/batch-method').'?ids='.implode(',', $payIds))
            ->with('status', '已批量切换 '.$affected.' 个支付通道到 '.$methodLabel.' 方式');
    }

    public function updateBatchName(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'pay_name' => ['required', 'string', 'max:255'],
        ]);

        $payIds = $this->payActionService->parsePayIds($validated['ids_text']);
        if (empty($payIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的支付通道 ID。'])
                ->withInput();
        }

        $payName = trim($validated['pay_name']);
        $affected = $this->payActionService->updateName($payIds, $payName);

        return redirect(admin_url('v2/pay/batch-name').'?ids='.implode(',', $payIds))
            ->with('status', '已批量更新 '.$affected.' 个支付通道的名称');
    }

    public function updateBatchNamePrefix(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'name_prefix' => ['required', 'string', 'max:100'],
        ]);

        $payIds = $this->payActionService->parsePayIds($validated['ids_text']);
        if (empty($payIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的支付通道 ID。'])
                ->withInput();
        }

        $prefix = trim($validated['name_prefix']);
        $affected = $this->payActionService->addNamePrefix($payIds, $prefix);

        return redirect(admin_url('v2/pay/batch-name-prefix').'?ids='.implode(',', $payIds))
            ->with('status', '已批量为 '.$affected.' 个支付通道添加名称前缀');
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
            'merchant_pem' => $isCreate ? ['required', 'string'] : ['nullable', 'string'],
            'pay_check' => $payCheckRules,
            'pay_client' => ['required', 'integer'],
            'pay_method' => ['required', 'integer'],
            'pay_handleroute' => ['required', 'string', 'max:255'],
        ]);

        return array_merge($payload, [
            'pay_client' => (int) $payload['pay_client'],
            'pay_method' => (int) $payload['pay_method'],
            'merchant_key' => $payload['merchant_key'] ?? '',
            'merchant_pem' => $payload['merchant_pem'] ?? '',
            'is_open' => $request->boolean('is_open') ? Pay::STATUS_OPEN : Pay::STATUS_CLOSE,
        ]);
    }

    private function resolveCopySource(Request $request): ?Pay
    {
        $copyId = $request->query('copy');

        if (blank($copyId)) {
            return null;
        }

        return Pay::query()->withTrashed()->findOrFail((int) $copyId);
    }
}
