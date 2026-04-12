<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Service\AdminSelectOptionService;
use App\Service\GoodsActionService;
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

    public function create()
    {
        return view('admin-shell.goods.form', [
            'title' => '新建商品 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '新建商品',
                'description' => '这是后台壳中的商品新建样板页。当前先承接商品基础信息、价格、库存、关联优惠码和配置文本，验证后台壳承接复杂业务编辑页的能力。',
                'meta' => '图片先按文本路径录入，暂不接入上传壳，优先保证商品创建与编辑主链稳定可用',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                ],
            ],
            'formAction' => admin_url('v2/goods/create'),
            'submitLabel' => '创建商品',
            'isCreate' => true,
            'defaults' => $this->goodsActionService->createDefaults(),
            'groupOptions' => $this->adminSelectOptionService->goodsGroupOptions(),
            'couponOptions' => $this->adminSelectOptionService->couponOptions(),
            'typeOptions' => Goods::getGoodsTypeMap(),
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
            'defaults' => $this->goodsActionService->editDefaults($goods),
            'groupOptions' => $this->adminSelectOptionService->goodsGroupOptions(),
            'couponOptions' => $this->adminSelectOptionService->couponOptions(),
            'typeOptions' => Goods::getGoodsTypeMap(),
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
