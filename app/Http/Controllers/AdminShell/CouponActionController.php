<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Service\AdminSelectOptionService;
use App\Service\CouponActionService;
use Illuminate\Http\Request;

class CouponActionController extends Controller
{
    /**
     * @var \App\Service\CouponActionService
     */
    private $couponActionService;

    /**
     * @var \App\Service\AdminSelectOptionService
     */
    private $adminSelectOptionService;

    public function __construct(CouponActionService $couponActionService, AdminSelectOptionService $adminSelectOptionService)
    {
        $this->couponActionService = $couponActionService;
        $this->adminSelectOptionService = $adminSelectOptionService;
    }

    public function create()
    {
        return view('admin-shell.coupon.form', [
            'title' => '新建优惠码 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '新建优惠码',
                'description' => '这是后台壳中的优惠码新建样板页。当前先承接优惠码的创建与商品关联配置，验证后台壳承接标准业务表单的能力。',
                'meta' => '优惠码创建后可继续在详情页和列表页中查看状态、关联商品和可用次数',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                ],
            ],
            'formAction' => admin_url('v2/coupon/create'),
            'submitLabel' => '创建优惠码',
            'isCreate' => true,
            'defaults' => $this->couponActionService->createDefaults(),
            'goodsOptions' => $this->adminSelectOptionService->goodsOptions(),
            'usageOptions' => Coupon::getStatusUseMap(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $coupon = $this->couponActionService->create($payload);

        return redirect(admin_url('v2/coupon/'.$coupon->id.'/edit'))
            ->with('status', '优惠码已创建');
    }

    public function edit(int $id)
    {
        $coupon = Coupon::query()->with('goods:id,gd_name')->findOrFail($id);

        return view('admin-shell.coupon.form', [
            'title' => '编辑优惠码 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => '编辑优惠码',
                'description' => '这是后台壳中的优惠码编辑样板页。当前复用普通 Laravel 控制器和服务处理折扣、状态与关联商品更新。',
                'meta' => '编辑后会同步更新关联商品与启用状态，不依赖旧 Dcat 表单壳',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '查看详情', 'href' => admin_url('v2/coupon/'.$coupon->id), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/'.$coupon->id.'/edit'),
            'submitLabel' => '保存优惠码',
            'isCreate' => false,
            'defaults' => $this->couponActionService->editDefaults($coupon),
            'goodsOptions' => $this->adminSelectOptionService->goodsOptions(),
            'usageOptions' => Coupon::getStatusUseMap(),
            'couponModel' => $coupon,
        ]);
    }

    public function update(int $id, Request $request)
    {
        $coupon = Coupon::query()->with('goods:id,gd_name')->findOrFail($id);
        $payload = $this->validatePayload($request);
        $this->couponActionService->update($coupon, $payload);

        return redirect(admin_url('v2/coupon/'.$coupon->id.'/edit'))
            ->with('status', '优惠码已保存');
    }

    private function validatePayload(Request $request): array
    {
        $payload = $request->validate([
            'goods_ids' => ['nullable', 'array'],
            'goods_ids.*' => ['integer', 'exists:goods,id'],
            'discount' => ['required', 'numeric', 'min:0'],
            'coupon' => ['required', 'string', 'max:255'],
            'ret' => ['required', 'integer', 'min:0'],
            'is_use' => ['required', 'integer'],
        ]);

        return array_merge($payload, [
            'goods_ids' => array_map('intval', $payload['goods_ids'] ?? []),
            'is_open' => $request->boolean('is_open') ? Coupon::STATUS_OPEN : Coupon::STATUS_CLOSE,
            'is_use' => (int) $payload['is_use'],
        ]);
    }
}
