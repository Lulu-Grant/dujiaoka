<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Service\AdminSelectOptionService;
use App\Service\GoodsActionService;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class GoodsActionController extends Controller
{
    /**
     * @var \App\Service\GoodsActionService
     */
    private $goodsActionService;

    /**
     * @var \App\Service\AdminSelectOptionService
     */
    private $adminSelectOptionService;

    public function __construct(GoodsActionService $goodsActionService, AdminSelectOptionService $adminSelectOptionService)
    {
        $this->goodsActionService = $goodsActionService;
        $this->adminSelectOptionService = $adminSelectOptionService;
    }

    public function create(Request $request)
    {
        $cloneSource = null;
        if ($request->filled('clone') && ctype_digit((string) $request->query('clone'))) {
            $cloneSource = Goods::query()->with(['group:id,gp_name', 'coupon:id,coupon'])->find((int) $request->query('clone'));
        }

        $defaults = $cloneSource
            ? $this->goodsActionService->cloneDefaults($cloneSource)
            : $this->goodsActionService->createDefaults();

        $actions = [
            ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
        ];

        if ($cloneSource) {
            $actions[] = ['label' => '查看原商品', 'href' => admin_url('v2/goods/'.$cloneSource->id), 'variant' => 'secondary'];
        }

        return view('admin-shell.goods.form', [
            'title' => $cloneSource ? '复制商品 - 后台壳样板' : '新建商品 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => $cloneSource ? '复制商品' : '新建商品',
                'description' => $cloneSource
                    ? '这是后台壳中的商品复制样板页。当前复用现有商品作为蓝本，管理员可以快速创建相似商品，并在提交前调整库存、销量和启用状态。'
                    : '这是后台壳中的商品新建样板页。当前先承接商品基础信息、价格、库存、关联优惠码和配置文本，验证后台壳承接复杂业务编辑页的能力。',
                'meta' => $cloneSource
                    ? '复制动作会保留分类、价格、优惠码和扩展配置，但会把库存、销量和启用状态重置成安全默认值。'
                    : '图片先按文本路径录入，暂不接入上传壳，优先保证商品创建与编辑主链稳定可用',
                'actions' => $actions,
            ],
            'formAction' => admin_url('v2/goods/create'),
            'submitLabel' => $cloneSource ? '复制商品' : '创建商品',
            'isCreate' => true,
            'sections' => $this->goodsActionService->formSections(
                $defaults,
                $this->adminSelectOptionService->goodsGroupOptions(),
                $this->adminSelectOptionService->couponOptions(),
                Goods::getGoodsTypeMap()
            ),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $goods = $this->goodsActionService->create($payload);

        return redirect(admin_url('v2/goods/'.$goods->id.'/edit'))
            ->with('status', '商品已创建');
    }

    public function edit(int $id)
    {
        $goods = Goods::query()->with(['group:id,gp_name', 'coupon:id,coupon'])->findOrFail($id);

        return view('admin-shell.goods.form', [
            'title' => '编辑商品 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑商品',
                'description' => '这是后台壳中的商品编辑样板页。当前复用普通 Laravel 控制器和服务处理商品资料、价格、库存与关联优惠码更新。',
                'meta' => '这张页会继续作为商品复杂编辑场景的迁移底座，后续再逐步承接上传、批量动作和更深的联动逻辑',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '查看详情', 'href' => admin_url('v2/goods/'.$goods->id), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/'.$goods->id.'/edit'),
            'submitLabel' => '保存商品',
            'isCreate' => false,
            'sections' => $this->goodsActionService->formSections(
                $this->goodsActionService->editDefaults($goods),
                $this->adminSelectOptionService->goodsGroupOptions(),
                $this->adminSelectOptionService->couponOptions(),
                Goods::getGoodsTypeMap()
            ),
            'goods' => $goods,
        ]);
    }

    public function update(int $id, Request $request)
    {
        $goods = Goods::query()->with(['group:id,gp_name', 'coupon:id,coupon'])->findOrFail($id);
        $payload = $this->validatePayload($request);
        $this->goodsActionService->update($goods, $payload);

        return redirect(admin_url('v2/goods/'.$goods->id.'/edit'))
            ->with('status', '商品已保存');
    }

    public function editBatchStatus(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchStatusDefaults($goodsIds);

        return view('admin-shell.goods.batch-status', [
            'title' => '批量启停商品 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量启停商品',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品启用状态切换，不触碰库存、价格和关联配置，先把高频运营动作收进新壳。',
                'meta' => '适合一口气处理活动下架、灰度上架和临时停售这类场景。输入商品 ID 即可执行，不依赖旧 Dcat 批量工具。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '新建商品', 'href' => admin_url('v2/goods/create'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/batch-status'),
            'submitLabel' => '执行批量状态更新',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchStatusContext($goodsIds),
        ]);
    }

    public function updateBatchStatus(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'is_open' => ['required', Rule::in([Goods::STATUS_OPEN, Goods::STATUS_CLOSE, '0', '1'])],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->updateOpenStatus($goodsIds, (int) $validated['is_open']);
        $statusLabel = (int) $validated['is_open'] === Goods::STATUS_OPEN ? '启用' : '停用';

        return redirect(admin_url('v2/goods/batch-status').'?ids='.implode(',', $goodsIds))
            ->with('status', '已批量'.$statusLabel.' '.$affected.' 个商品');
    }

    private function validatePayload(Request $request): array
    {
        $payload = $request->validate([
            'group_id' => ['required', 'integer', 'exists:goods_group,id'],
            'coupon_ids' => ['nullable', 'array'],
            'coupon_ids.*' => ['integer', 'exists:coupons,id'],
            'gd_name' => ['required', 'string', 'max:255'],
            'gd_description' => ['required', 'string', 'max:255'],
            'gd_keywords' => ['required', 'string', 'max:255'],
            'picture' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'integer'],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'actual_price' => ['required', 'numeric', 'min:0'],
            'in_stock' => ['required', 'integer', 'min:0'],
            'sales_volume' => ['required', 'integer', 'min:0'],
            'buy_limit_num' => ['required', 'integer', 'min:0'],
            'buy_prompt' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'other_ipu_cnf' => ['nullable', 'string'],
            'wholesale_price_cnf' => ['nullable', 'string'],
            'api_hook' => ['nullable', 'string'],
            'ord' => ['required', 'integer', 'min:0'],
        ]);

        return array_merge($payload, [
            'group_id' => (int) $payload['group_id'],
            'coupon_ids' => array_map('intval', $payload['coupon_ids'] ?? []),
            'type' => (int) $payload['type'],
            'retail_price' => (float) $payload['retail_price'],
            'actual_price' => (float) $payload['actual_price'],
            'in_stock' => (int) $payload['in_stock'],
            'sales_volume' => (int) $payload['sales_volume'],
            'buy_limit_num' => (int) $payload['buy_limit_num'],
            'ord' => (int) $payload['ord'],
            'picture' => (string) ($payload['picture'] ?? ''),
            'buy_prompt' => (string) ($payload['buy_prompt'] ?? ''),
            'description' => (string) ($payload['description'] ?? ''),
            'other_ipu_cnf' => (string) ($payload['other_ipu_cnf'] ?? ''),
            'wholesale_price_cnf' => (string) ($payload['wholesale_price_cnf'] ?? ''),
            'api_hook' => (string) ($payload['api_hook'] ?? ''),
            'is_open' => $request->boolean('is_open') ? Goods::STATUS_OPEN : Goods::STATUS_CLOSE,
        ]);
    }
}
