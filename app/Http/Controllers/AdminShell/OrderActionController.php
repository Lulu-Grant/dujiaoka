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
}
