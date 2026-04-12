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

    public function create(Request $request)
    {
        $batchMode = $this->isBatchMode($request);

        return view($batchMode ? 'admin-shell.coupon.batch' : 'admin-shell.coupon.form', [
            'title' => '新建优惠码 - 后台壳样板',
            'header' => $this->buildHeader($batchMode),
            'couponCodePrefix' => $this->couponActionService->couponCodePrefix(),
            'suggestedCouponCode' => $this->couponActionService->suggestCouponCode(),
            'formGuide' => $batchMode
                ? '批量模式会一次生成多条优惠码，适合发放活动码、测试码和分组优惠码。'
                : '建议先生成一个可识别的优惠码，再选择关联商品。后续查单、测试和核对都会更快。',
            'formAction' => admin_url('v2/coupon/create').($batchMode ? '?mode=batch' : ''),
            'submitLabel' => $batchMode ? '批量生成优惠码' : '创建优惠码',
            'isCreate' => true,
            'isBatchMode' => $batchMode,
            'defaults' => $batchMode ? $this->couponActionService->batchCreateDefaults() : $this->couponActionService->createDefaults(),
            'goodsOptions' => $this->adminSelectOptionService->goodsOptions(),
            'usageOptions' => Coupon::getStatusUseMap(),
        ]);
    }

    public function store(Request $request)
    {
        if ($this->isBatchMode($request)) {
            $payload = $this->validateBatchPayload($request);
            $coupons = $this->couponActionService->createBatch($payload);

            return redirect('/admin/v2/coupon')
                ->with('status', '已批量生成 '.$coupons->count().' 个优惠码');
        }

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
            'header' => $this->buildEditHeader($coupon),
            'couponCodePrefix' => $this->couponActionService->couponCodePrefix(),
            'suggestedCouponCode' => $this->couponActionService->suggestCouponCode(),
            'formGuide' => '编辑时建议先确认关联商品和可用次数，再保存低风险维护字段。',
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

    private function validateBatchPayload(Request $request): array
    {
        $payload = $request->validate([
            'goods_ids' => ['nullable', 'array'],
            'goods_ids.*' => ['integer', 'exists:goods,id'],
            'discount' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1', 'max:200'],
            'prefix' => ['nullable', 'string', 'max:64'],
            'length' => ['required', 'integer', 'min:4', 'max:32'],
            'ret' => ['required', 'integer', 'min:0'],
            'is_use' => ['required', 'integer'],
        ]);

        return array_merge($payload, [
            'goods_ids' => array_map('intval', $payload['goods_ids'] ?? []),
            'quantity' => (int) $payload['quantity'],
            'prefix' => trim((string) ($payload['prefix'] ?? '')),
            'length' => (int) $payload['length'],
            'is_open' => $request->boolean('is_open') ? Coupon::STATUS_OPEN : Coupon::STATUS_CLOSE,
            'is_use' => (int) $payload['is_use'],
        ]);
    }

    private function isBatchMode(Request $request): bool
    {
        return $request->query('mode') === 'batch' || $request->input('mode') === 'batch';
    }

    private function buildHeader(bool $batchMode): array
    {
        return [
            'kicker' => 'Admin Shell Action',
            'title' => $batchMode ? '批量生成优惠码' : '新建优惠码',
            'description' => $batchMode
                ? '这是后台壳中的优惠码批量生成样板页。当前支持一次生成多条优惠码，适合活动发放和测试批量建码。'
                : '这是后台壳中的优惠码新建样板页。当前先承接优惠码的创建与商品关联配置，验证后台壳承接标准业务表单的能力。',
            'meta' => $batchMode
                ? '批量生成后会自动写入优惠码、折扣、可用次数和关联商品'
                : '优惠码创建后可继续在详情页和列表页中查看状态、关联商品和可用次数',
            'actions' => [
                ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                ['label' => $batchMode ? '切回单个创建' : '批量生成', 'href' => admin_url('v2/coupon/create').($batchMode ? '' : '?mode=batch'), 'variant' => 'secondary'],
            ],
        ];
    }

    private function buildEditHeader(Coupon $coupon): array
    {
        return [
            'kicker' => 'Admin Shell Action',
            'title' => '编辑优惠码',
            'description' => '这是后台壳中的优惠码编辑样板页。当前复用普通 Laravel 控制器和服务处理折扣、状态与关联商品更新。',
            'meta' => '编辑后会同步更新关联商品与启用状态，不依赖旧 Dcat 表单壳',
            'actions' => [
                ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                ['label' => '查看详情', 'href' => admin_url('v2/coupon/'.$coupon->id), 'variant' => 'secondary'],
            ],
        ];
    }
}
