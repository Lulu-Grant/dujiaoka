<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Service\OrderActionService;
use Illuminate\Http\Request;

class OrderActionController extends Controller
{
    /**
     * @var \App\Service\OrderActionService
     */
    private $orderActionService;

    public function __construct(OrderActionService $orderActionService)
    {
        $this->orderActionService = $orderActionService;
    }

    public function edit(int $id)
    {
        $order = Order::query()->with(['goods:id,gd_name', 'coupon:id,coupon', 'pay:id,pay_name'])->findOrFail($id);

        return view('admin-shell.order.form', [
            'title' => '编辑订单 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑订单',
                'description' => '这是后台壳中的订单编辑样板页。页面已按基础信息、交易与履约、维护提醒分组，当前仅允许修改标题、附加信息、状态、查询密码和订单类型，并提供重置查询密码的安全维护动作。',
                'meta' => '低风险人工维护入口已迁入后台壳，分组上下文方便快速确认订单背景，再决定是否保存或重置查询密码。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                    ['label' => '查看详情', 'href' => admin_url('v2/order/'.$order->id), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/order/'.$order->id.'/edit'),
            'submitLabel' => '保存订单',
            'defaults' => $this->orderActionService->editDefaults($order),
            'context' => $this->orderActionService->editContext($order),
            'statusOptions' => Order::getStatusMap(),
            'typeOptions' => Order::getTypeMap(),
            'order' => $order,
        ]);
    }

    public function update(int $id, Request $request)
    {
        $order = Order::query()->findOrFail($id);

        if ($request->boolean('reset_search_pwd')) {
            $newPassword = $this->orderActionService->resetSearchPassword($order);

            return redirect(admin_url('v2/order/'.$order->id.'/edit'))
                ->with('status', '订单查询密码已重置为 '.$newPassword);
        }

        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'info' => ['nullable', 'string'],
            'status' => ['required', 'integer'],
            'search_pwd' => ['required', 'string', 'max:255'],
            'type' => ['required', 'integer'],
        ]);

        $this->orderActionService->update($order, $payload);

        return redirect(admin_url('v2/order/'.$order->id.'/edit'))
            ->with('status', '订单已保存');
    }

    public function editBatchStatus(Request $request)
    {
        $orderIds = $this->orderActionService->parseOrderIds((string) $request->query('ids', ''));
        $defaults = $this->orderActionService->batchStatusDefaults($orderIds);
        $context = $this->orderActionService->batchStatusContext($orderIds);

        return view('admin-shell.order.batch-status', [
            'title' => '批量更新订单状态 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量更新订单状态',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接订单状态的人工维护，不触碰支付、履约和通知链。',
                'meta' => '适合人工整理异常订单、统一切换处理中状态或做运营兜底。提交后只更新订单状态字段，并保持无事件写入。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                    ['label' => '批量重置查询密码', 'href' => admin_url('v2/order/batch-reset-search-pwd'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/order/batch-status'),
            'submitLabel' => '执行批量状态更新',
            'defaults' => $defaults,
            'context' => $context,
            'statusOptions' => Order::getStatusMap(),
        ]);
    }

    public function editBatchType(Request $request)
    {
        $orderIds = $this->orderActionService->parseOrderIds((string) $request->query('ids', ''));
        $defaults = $this->orderActionService->batchTypeDefaults($orderIds);
        $context = $this->orderActionService->batchStatusContext($orderIds);

        return view('admin-shell.order.batch-type', [
            'title' => '批量设置订单类型 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置订单类型',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接订单类型的人工维护，不触碰状态、支付、履约和通知链。',
                'meta' => '适合人工纠偏自动发货/人工处理类型，或统一整理一批历史订单。提交后只更新订单类型字段，并保持无事件写入。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                    ['label' => '批量更新订单状态', 'href' => admin_url('v2/order/batch-status'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/order/batch-type'),
            'submitLabel' => '执行批量类型更新',
            'defaults' => $defaults,
            'context' => $context,
            'typeOptions' => Order::getTypeMap(),
        ]);
    }

    public function editBatchInfo(Request $request)
    {
        $orderIds = $this->orderActionService->parseOrderIds((string) $request->query('ids', ''));
        $defaults = $this->orderActionService->batchInfoDefaults($orderIds);
        $context = $this->orderActionService->batchStatusContext($orderIds);

        return view('admin-shell.order.batch-info', [
            'title' => '批量设置订单附加信息 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置订单附加信息',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接订单附加信息的人工维护，不触碰状态、类型、支付、履约和通知链。',
                'meta' => '适合统一补充运营备注、售后说明或人工核对标记。提交后只更新订单附加信息字段，并保持无事件写入。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                    ['label' => '批量设置订单类型', 'href' => admin_url('v2/order/batch-type'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/order/batch-info'),
            'submitLabel' => '执行批量附加信息更新',
            'defaults' => $defaults,
            'context' => $context,
        ]);
    }

    public function editBatchTitle(Request $request)
    {
        $orderIds = $this->orderActionService->parseOrderIds((string) $request->query('ids', ''));
        $defaults = $this->orderActionService->batchTitleDefaults($orderIds);
        $context = $this->orderActionService->batchStatusContext($orderIds);

        return view('admin-shell.order.batch-title', [
            'title' => '批量设置订单标题 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置订单标题',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接订单标题的人工维护，不触碰状态、类型、支付、履约和通知链。',
                'meta' => '适合统一补充活动标签、人工复核标记或整理一批历史订单标题。提交后只更新订单标题字段，并保持无事件写入。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                    ['label' => '批量设置附加信息', 'href' => admin_url('v2/order/batch-info'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/order/batch-title'),
            'submitLabel' => '执行批量标题更新',
            'defaults' => $defaults,
            'context' => $context,
        ]);
    }

    public function editBatchTitlePrefix(Request $request)
    {
        $orderIds = $this->orderActionService->parseOrderIds((string) $request->query('ids', ''));
        $defaults = $this->orderActionService->batchTitlePrefixDefaults($orderIds);
        $context = $this->orderActionService->batchStatusContext($orderIds);

        return view('admin-shell.order.batch-title-prefix', [
            'title' => '批量添加订单标题前缀 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量添加订单标题前缀',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接订单标题前缀的人工维护，不触碰状态、类型、支付、履约和通知链。',
                'meta' => '适合统一补充活动标签、售后复核标记或历史订单整理标识。提交后只更新订单标题字段，并保持无事件写入。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                    ['label' => '批量设置订单标题', 'href' => admin_url('v2/order/batch-title'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/order/batch-title-prefix'),
            'submitLabel' => '执行标题前缀更新',
            'defaults' => $defaults,
            'context' => $context,
        ]);
    }

    public function editBatchTitleSuffix(Request $request)
    {
        $orderIds = $this->orderActionService->parseOrderIds((string) $request->query('ids', ''));
        $defaults = $this->orderActionService->batchTitleSuffixDefaults($orderIds);
        $context = $this->orderActionService->batchStatusContext($orderIds);

        return view('admin-shell.order.batch-title-suffix', [
            'title' => '批量添加订单标题后缀 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量添加订单标题后缀',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接订单标题后缀的人工维护，不触碰状态、类型、支付、履约和通知链。',
                'meta' => '适合统一补充活动尾标、售后结案标记或历史订单整理说明。提交后只更新订单标题字段，并保持无事件写入。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                    ['label' => '批量添加标题前缀', 'href' => admin_url('v2/order/batch-title-prefix'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/order/batch-title-suffix'),
            'submitLabel' => '执行标题后缀更新',
            'defaults' => $defaults,
            'context' => $context,
        ]);
    }

    public function updateBatchStatus(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'status' => ['required', 'integer'],
        ]);

        $orderIds = $this->orderActionService->parseOrderIds($validated['ids_text']);

        if (empty($orderIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的订单 ID。'])
                ->withInput();
        }

        $updated = $this->orderActionService->updateStatuses($orderIds, (int) $validated['status']);

        if ($updated === 0) {
            return redirect()->back()
                ->withErrors(['ids_text' => '没有找到可更新状态的订单。'])
                ->withInput();
        }

        $statusLabel = Order::getStatusMap()[(int) $validated['status']] ?? (string) $validated['status'];

        return redirect(admin_url('v2/order/batch-status').'?ids='.implode(',', $orderIds))
            ->with('status', '已批量更新 '.$updated.' 个订单的状态为 '.$statusLabel);
    }

    public function updateBatchType(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'type' => ['required', 'integer'],
        ]);

        $orderIds = $this->orderActionService->parseOrderIds($validated['ids_text']);

        if (empty($orderIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的订单 ID。'])
                ->withInput();
        }

        $updated = $this->orderActionService->updateTypes($orderIds, (int) $validated['type']);

        if ($updated === 0) {
            return redirect()->back()
                ->withErrors(['ids_text' => '没有找到可更新类型的订单。'])
                ->withInput();
        }

        $typeLabel = Order::getTypeMap()[(int) $validated['type']] ?? (string) $validated['type'];

        return redirect(admin_url('v2/order/batch-type').'?ids='.implode(',', $orderIds))
            ->with('status', '已批量更新 '.$updated.' 个订单的类型为 '.$typeLabel);
    }

    public function updateBatchInfo(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'info' => ['nullable', 'string'],
        ]);

        $orderIds = $this->orderActionService->parseOrderIds($validated['ids_text']);

        if (empty($orderIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的订单 ID。'])
                ->withInput();
        }

        $updated = $this->orderActionService->updateInfos($orderIds, (string) ($validated['info'] ?? ''));

        if ($updated === 0) {
            return redirect()->back()
                ->withErrors(['ids_text' => '没有找到可更新附加信息的订单。'])
                ->withInput();
        }

        return redirect(admin_url('v2/order/batch-info').'?ids='.implode(',', $orderIds))
            ->with('status', '已批量更新 '.$updated.' 个订单的附加信息');
    }

    public function updateBatchTitle(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $orderIds = $this->orderActionService->parseOrderIds($validated['ids_text']);

        if (empty($orderIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的订单 ID。'])
                ->withInput();
        }

        $updated = $this->orderActionService->updateTitles($orderIds, $validated['title']);

        if ($updated === 0) {
            return redirect()->back()
                ->withErrors(['ids_text' => '没有找到可更新标题的订单。'])
                ->withInput();
        }

        return redirect(admin_url('v2/order/batch-title').'?ids='.implode(',', $orderIds))
            ->with('status', '已批量更新 '.$updated.' 个订单的标题');
    }

    public function updateBatchTitlePrefix(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'title_prefix' => ['required', 'string', 'max:64'],
        ]);

        $orderIds = $this->orderActionService->parseOrderIds($validated['ids_text']);

        if (empty($orderIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的订单 ID。'])
                ->withInput();
        }

        $updated = $this->orderActionService->addTitlePrefix($orderIds, $validated['title_prefix']);

        if ($updated === 0) {
            return redirect()->back()
                ->withErrors(['ids_text' => '没有找到可更新标题前缀的订单。'])
                ->withInput();
        }

        return redirect(admin_url('v2/order/batch-title-prefix').'?ids='.implode(',', $orderIds))
            ->with('status', '已批量为 '.$updated.' 个订单标题添加前缀');
    }

    public function updateBatchTitleSuffix(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'title_suffix' => ['required', 'string', 'max:64'],
        ]);

        $orderIds = $this->orderActionService->parseOrderIds($validated['ids_text']);

        if (empty($orderIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的订单 ID。'])
                ->withInput();
        }

        $updated = $this->orderActionService->addTitleSuffix($orderIds, $validated['title_suffix']);

        if ($updated === 0) {
            return redirect()->back()
                ->withErrors(['ids_text' => '没有找到可更新标题后缀的订单。'])
                ->withInput();
        }

        return redirect(admin_url('v2/order/batch-title-suffix').'?ids='.implode(',', $orderIds))
            ->with('status', '已批量为 '.$updated.' 个订单标题添加后缀');
    }

    public function batchResetSearchPassword(Request $request)
    {
        $orderIds = $this->orderActionService->parseOrderIds((string) $request->query('ids', ''));
        $defaults = $this->orderActionService->batchResetDefaults($orderIds);
        $context = $this->orderActionService->batchResetContext($orderIds);

        return view('admin-shell.order.batch-reset-search-pwd', [
            'title' => '批量重置订单查询密码 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量重置订单查询密码',
                'description' => '这是后台壳中的低风险批量动作页。页面先预览匹配到的订单，再统一重置查询密码，不触碰订单状态、支付或履约链。',
                'meta' => '支持换行、逗号和空格分隔的订单 ID。提交后只会逐个刷新查询密码，适合批量排查、人工回收或安全维护。',
                'actions' => [
                    ['label' => '返回订单概览', 'href' => admin_url('v2/order')],
                ],
            ],
            'formAction' => admin_url('v2/order/batch-reset-search-pwd'),
            'submitLabel' => '重置匹配订单的查询密码',
            'defaults' => $defaults,
            'context' => $context,
        ]);
    }

    public function updateBatchResetSearchPassword(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
        ]);

        $orderIds = $this->orderActionService->parseOrderIds($validated['ids_text']);

        if (empty($orderIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的订单 ID。'])
                ->withInput();
        }

        $updated = $this->orderActionService->batchResetSearchPasswords($orderIds);

        if ($updated === 0) {
            return redirect()->back()
                ->withErrors(['ids_text' => '没有找到可重置查询密码的订单。'])
                ->withInput();
        }

        return redirect(admin_url('v2/order/batch-reset-search-pwd').'?ids='.implode(',', $orderIds))
            ->with('status', '已批量重置 '.$updated.' 个订单的查询密码');
    }
}
