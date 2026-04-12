<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\GoodsGroup;
use App\Service\GoodsGroupActionService;
use Illuminate\Http\Request;

class GoodsGroupActionController extends Controller
{
    /**
     * @var \App\Service\GoodsGroupActionService
     */
    private $goodsGroupActionService;

    public function __construct(GoodsGroupActionService $goodsGroupActionService)
    {
        $this->goodsGroupActionService = $goodsGroupActionService;
    }

    public function create()
    {
        return view('admin-shell.goods-group.form', [
            'title' => '新建商品分类 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '新建商品分类',
                'description' => '这是后台壳中的商品分类新建样板页。当前先承接分类名称、启用状态和排序字段，验证后台壳承接低风险标准表单的能力。',
                'meta' => '商品分类是后台最基础的管理对象之一，适合作为标准 CRUD 样板继续扩展',
                'actions' => [
                    ['label' => '返回商品分类概览', 'href' => admin_url('v2/goods-group')],
                ],
            ],
            'formAction' => admin_url('v2/goods-group/create'),
            'submitLabel' => '创建商品分类',
            'defaults' => $this->goodsGroupActionService->createDefaults(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $group = $this->goodsGroupActionService->create($payload);

        return redirect(admin_url('v2/goods-group/'.$group->id.'/edit'))
            ->with('status', '商品分类已创建');
    }

    public function edit(int $id)
    {
        $group = GoodsGroup::query()->findOrFail($id);

        return view('admin-shell.goods-group.form', [
            'title' => '编辑商品分类 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑商品分类',
                'description' => '这是后台壳中的商品分类编辑样板页。当前复用普通 Laravel 控制器和服务处理分类基础信息更新。',
                'meta' => '编辑后会同步更新分类名称、启用状态与排序，不再依赖旧 Dcat 表单壳',
                'actions' => [
                    ['label' => '返回商品分类概览', 'href' => admin_url('v2/goods-group')],
                    ['label' => '查看详情', 'href' => admin_url('v2/goods-group/'.$group->id), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods-group/'.$group->id.'/edit'),
            'submitLabel' => '保存商品分类',
            'defaults' => $this->goodsGroupActionService->editDefaults($group),
            'group' => $group,
        ]);
    }

    public function update(int $id, Request $request)
    {
        $group = GoodsGroup::query()->findOrFail($id);
        $payload = $this->validatePayload($request);
        $this->goodsGroupActionService->update($group, $payload);

        return redirect(admin_url('v2/goods-group/'.$group->id.'/edit'))
            ->with('status', '商品分类已保存');
    }

    private function validatePayload(Request $request): array
    {
        $payload = $request->validate([
            'gp_name' => ['required', 'string', 'max:255'],
            'ord' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        return array_merge($payload, [
            'is_open' => $request->boolean('is_open') ? GoodsGroup::STATUS_OPEN : GoodsGroup::STATUS_CLOSE,
        ]);
    }
}
