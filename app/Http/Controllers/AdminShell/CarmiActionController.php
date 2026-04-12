<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Carmis;
use App\Service\AdminSelectOptionService;
use App\Service\CarmiActionService;
use Illuminate\Http\Request;

class CarmiActionController extends Controller
{
    /**
     * @var \App\Service\CarmiActionService
     */
    private $carmiActionService;

    /**
     * @var \App\Service\AdminSelectOptionService
     */
    private $adminSelectOptionService;

    public function __construct(CarmiActionService $carmiActionService, AdminSelectOptionService $adminSelectOptionService)
    {
        $this->carmiActionService = $carmiActionService;
        $this->adminSelectOptionService = $adminSelectOptionService;
    }

    public function create()
    {
        return view('admin-shell.carmis.form', [
            'title' => '新建卡密 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '新建卡密',
                'description' => '这是后台壳中的卡密新建样板页。当前先承接关联自动发货商品、销售状态、循环使用标记和卡密内容。',
                'meta' => '这张页与导入页互补，适合补录单条卡密或修复异常库存数据',
                'actions' => [
                    ['label' => '返回卡密概览', 'href' => admin_url('v2/carmis')],
                    ['label' => '进入旧版功能页', 'href' => admin_url('carmis'), 'variant' => 'primary'],
                ],
            ],
            'formAction' => admin_url('v2/carmis/create'),
            'submitLabel' => '创建卡密',
            'isCreate' => true,
            'defaults' => $this->carmiActionService->createDefaults(),
            'goodsOptions' => $this->adminSelectOptionService->automaticGoodsOptions(),
            'statusOptions' => Carmis::getStatusMap(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $carmi = $this->carmiActionService->create($payload);

        return redirect(admin_url('v2/carmis/'.$carmi->id.'/edit'))
            ->with('status', '卡密已创建');
    }

    public function edit(int $id)
    {
        $carmi = Carmis::query()->with('goods:id,gd_name')->findOrFail($id);

        return view('admin-shell.carmis.form', [
            'title' => '编辑卡密 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑卡密',
                'description' => '这是后台壳中的卡密编辑样板页。当前复用普通 Laravel 控制器和服务处理卡密内容、状态与循环使用标记更新。',
                'meta' => '卡密编辑优先用于修复库存与履约数据，不直接触发订单主链动作',
                'actions' => [
                    ['label' => '返回卡密概览', 'href' => admin_url('v2/carmis')],
                    ['label' => '查看详情', 'href' => admin_url('v2/carmis/'.$carmi->id), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/carmis/'.$carmi->id.'/edit'),
            'submitLabel' => '保存卡密',
            'isCreate' => false,
            'defaults' => $this->carmiActionService->editDefaults($carmi),
            'goodsOptions' => $this->adminSelectOptionService->automaticGoodsOptions(),
            'statusOptions' => Carmis::getStatusMap(),
            'carmiModel' => $carmi,
        ]);
    }

    public function update(int $id, Request $request)
    {
        $carmi = Carmis::query()->with('goods:id,gd_name')->findOrFail($id);
        $payload = $this->validatePayload($request);
        $this->carmiActionService->update($carmi, $payload);

        return redirect(admin_url('v2/carmis/'.$carmi->id.'/edit'))
            ->with('status', '卡密已保存');
    }

    private function validatePayload(Request $request): array
    {
        $payload = $request->validate([
            'goods_id' => ['required', 'integer', 'exists:goods,id'],
            'status' => ['required', 'integer'],
            'carmi' => ['required', 'string'],
        ]);

        return array_merge($payload, [
            'goods_id' => (int) $payload['goods_id'],
            'status' => (int) $payload['status'],
            'is_loop' => $request->boolean('is_loop') ? 1 : 0,
        ]);
    }
}
