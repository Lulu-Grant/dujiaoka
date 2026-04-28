<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Service\AdminSelectOptionService;
use App\Service\CouponActionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        if ($this->isBatchStatusMode($request)) {
            return $this->renderBatchStatusPage($request);
        }

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
        if ($this->isBatchStatusMode($request)) {
            return $this->storeBatchStatus($request);
        }

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

    public function editBatchStatus(Request $request)
    {
        return $this->renderBatchStatusPage($request);
    }

    public function updateBatchStatus(Request $request)
    {
        return $this->storeBatchStatus($request);
    }

    public function editBatchRet(Request $request)
    {
        return $this->renderBatchRetPage($request);
    }

    public function editBatchUse(Request $request)
    {
        return $this->renderBatchUsePage($request);
    }

    public function editBatchDiscount(Request $request)
    {
        return $this->renderBatchDiscountPage($request);
    }

    public function editBatchCode(Request $request)
    {
        return $this->renderBatchCodePage($request);
    }

    public function editBatchCodePrefix(Request $request)
    {
        return $this->renderBatchCodePrefixPage($request);
    }

    public function editBatchCodeSuffix(Request $request)
    {
        return $this->renderBatchCodeSuffixPage($request);
    }

    public function updateBatchRet(Request $request)
    {
        return $this->storeBatchRet($request);
    }

    public function updateBatchUse(Request $request)
    {
        return $this->storeBatchUse($request);
    }

    public function updateBatchDiscount(Request $request)
    {
        return $this->storeBatchDiscount($request);
    }

    public function updateBatchCode(Request $request)
    {
        return $this->storeBatchCode($request);
    }

    public function updateBatchCodePrefix(Request $request)
    {
        return $this->storeBatchCodePrefix($request);
    }

    public function updateBatchCodeSuffix(Request $request)
    {
        return $this->storeBatchCodeSuffix($request);
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

    private function renderBatchStatusPage(Request $request)
    {
        $couponIds = $this->couponActionService->parseCouponIds((string) $request->query('ids', $request->input('ids_text', '')));
        $defaults = $this->couponActionService->batchStatusDefaults($couponIds);

        return view('admin-shell.coupon.batch-status', [
            'title' => '批量启停优惠码 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量启停优惠码',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接优惠码启用状态切换，不改折扣、次数和关联商品，优先把高频维护动作收进新壳。',
                'meta' => '支持换行、逗号或空格分隔的 ID 输入。先预览匹配结果，再提交批量启用或停用。',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '批量生成优惠码', 'href' => admin_url('v2/coupon/create?mode=batch'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/batch-status'),
            'submitLabel' => '执行批量状态更新',
            'defaults' => $defaults,
            'context' => $this->couponActionService->batchStatusContext($couponIds),
        ]);
    }

    private function storeBatchStatus(Request $request)
    {
        $payload = $request->validate([
            'ids_text' => ['required', 'string'],
            'is_open' => ['required', Rule::in([Coupon::STATUS_OPEN, Coupon::STATUS_CLOSE, '0', '1'])],
        ]);

        $couponIds = $this->couponActionService->parseCouponIds($payload['ids_text']);
        if (empty($couponIds)) {
            return redirect()
                ->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的优惠码 ID。'])
                ->withInput();
        }

        $affected = $this->couponActionService->updateOpenStatus($couponIds, (int) $payload['is_open']);
        $statusLabel = (int) $payload['is_open'] === Coupon::STATUS_OPEN ? '启用' : '停用';

        return redirect(admin_url('v2/coupon/batch-status?ids='.implode(',', $couponIds)))
            ->with('status', '已批量'.$statusLabel.' '.$affected.' 个优惠码');
    }

    private function renderBatchRetPage(Request $request)
    {
        $couponIds = $this->couponActionService->parseCouponIds((string) $request->query('ids', $request->input('ids_text', '')));
        $defaults = $this->couponActionService->batchRetDefaults($couponIds);

        return view('admin-shell.coupon.batch-ret', [
            'title' => '批量设置优惠码可用次数 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置优惠码可用次数',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接优惠码可用次数调整，不改折扣、启用状态和关联商品，优先把常见运营维护动作收进新壳。',
                'meta' => '适合活动码补量、测试码限次和人工纠偏。支持换行、逗号或空格分隔的 ID 输入，先预览再统一提交。',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '批量启停优惠码', 'href' => admin_url('v2/coupon/batch-status'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/batch-ret'),
            'submitLabel' => '执行批量次数更新',
            'defaults' => $defaults,
            'context' => $this->couponActionService->batchStatusContext($couponIds),
        ]);
    }

    private function renderBatchUsePage(Request $request)
    {
        $couponIds = $this->couponActionService->parseCouponIds((string) $request->query('ids', $request->input('ids_text', '')));
        $defaults = $this->couponActionService->batchUseDefaults($couponIds);

        return view('admin-shell.coupon.batch-use', [
            'title' => '批量设置优惠码使用状态 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置优惠码使用状态',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接优惠码使用状态切换，不改折扣、可用次数、启用状态和关联商品。',
                'meta' => '适合人工纠偏测试码、恢复误标记状态或统一标记一批已用优惠码。支持换行、逗号或空格分隔的 ID 输入。',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '批量设置可用次数', 'href' => admin_url('v2/coupon/batch-ret'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/batch-use'),
            'submitLabel' => '执行批量使用状态更新',
            'defaults' => $defaults,
            'context' => $this->couponActionService->batchStatusContext($couponIds),
            'usageOptions' => Coupon::getStatusUseMap(),
        ]);
    }

    private function renderBatchDiscountPage(Request $request)
    {
        $couponIds = $this->couponActionService->parseCouponIds((string) $request->query('ids', $request->input('ids_text', '')));
        $defaults = $this->couponActionService->batchDiscountDefaults($couponIds);

        return view('admin-shell.coupon.batch-discount', [
            'title' => '批量设置优惠码折扣 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置优惠码折扣',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接优惠码折扣调整，不改使用状态、可用次数、启用状态和关联商品。',
                'meta' => '适合活动期统一调价、测试券批量修正或运营快速纠偏。支持换行、逗号或空格分隔的 ID 输入。',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '批量设置使用状态', 'href' => admin_url('v2/coupon/batch-use'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/batch-discount'),
            'submitLabel' => '执行批量折扣更新',
            'defaults' => $defaults,
            'context' => $this->couponActionService->batchStatusContext($couponIds),
        ]);
    }

    private function renderBatchCodePage(Request $request)
    {
        $couponIds = $this->couponActionService->parseCouponIds((string) $request->query('ids', $request->input('ids_text', '')));
        $defaults = $this->couponActionService->batchCodeDefaults($couponIds);

        return view('admin-shell.coupon.batch-code', [
            'title' => '批量重生成优惠码内容 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量重生成优惠码内容',
                'description' => '这是后台壳中的低风险维护页。当前只承接优惠码内容重生成，不改折扣、可用次数、启用状态、使用状态和关联商品。',
                'meta' => '适合统一更新活动期优惠码前缀、重整测试码样式或人工换码。提交后会按前缀和长度逐个生成新的唯一优惠码内容。',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '批量设置折扣', 'href' => admin_url('v2/coupon/batch-discount'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/batch-code'),
            'submitLabel' => '执行批量换码',
            'defaults' => $defaults,
            'context' => $this->couponActionService->batchStatusContext($couponIds),
        ]);
    }

    private function renderBatchCodePrefixPage(Request $request)
    {
        $couponIds = $this->couponActionService->parseCouponIds((string) $request->query('ids', $request->input('ids_text', '')));
        $defaults = $this->couponActionService->batchCodePrefixDefaults($couponIds);

        return view('admin-shell.coupon.batch-code-prefix', [
            'title' => '批量添加优惠码前缀 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量添加优惠码前缀',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接优惠码内容的运营前缀补充，不改折扣、可用次数、启用状态、使用状态和关联商品。',
                'meta' => '适合活动期统一标记渠道批次、投放来源或临时测试前缀。支持换行、逗号或空格分隔的 ID 输入。',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '批量重生成优惠码', 'href' => admin_url('v2/coupon/batch-code'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/batch-code-prefix'),
            'submitLabel' => '执行批量前缀更新',
            'defaults' => $defaults,
            'context' => $this->couponActionService->batchStatusContext($couponIds),
        ]);
    }

    private function renderBatchCodeSuffixPage(Request $request)
    {
        $couponIds = $this->couponActionService->parseCouponIds((string) $request->query('ids', $request->input('ids_text', '')));
        $defaults = $this->couponActionService->batchCodeSuffixDefaults($couponIds);

        return view('admin-shell.coupon.batch-code-suffix', [
            'title' => '批量添加优惠码后缀 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量添加优惠码后缀',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接优惠码内容的运营后缀补充，不改折扣、可用次数、启用状态、使用状态和关联商品。',
                'meta' => '适合活动结束标记、渠道批次归档或临时测试后缀。支持换行、逗号或空格分隔的 ID 输入。',
                'actions' => [
                    ['label' => '返回优惠码概览', 'href' => admin_url('v2/coupon')],
                    ['label' => '批量添加优惠码前缀', 'href' => admin_url('v2/coupon/batch-code-prefix'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/coupon/batch-code-suffix'),
            'submitLabel' => '执行批量后缀更新',
            'defaults' => $defaults,
            'context' => $this->couponActionService->batchStatusContext($couponIds),
        ]);
    }

    private function storeBatchRet(Request $request)
    {
        $payload = $request->validate([
            'ids_text' => ['required', 'string'],
            'ret' => ['required', 'integer', 'min:0'],
        ]);

        $couponIds = $this->couponActionService->parseCouponIds($payload['ids_text']);
        if (empty($couponIds)) {
            return redirect()
                ->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的优惠码 ID。'])
                ->withInput();
        }

        $ret = (int) $payload['ret'];
        $affected = $this->couponActionService->updateRet($couponIds, $ret);

        return redirect(admin_url('v2/coupon/batch-ret?ids='.implode(',', $couponIds)))
            ->with('status', '已批量把 '.$affected.' 个优惠码的可用次数调整为 '.$ret.' 次');
    }

    private function storeBatchUse(Request $request)
    {
        $payload = $request->validate([
            'ids_text' => ['required', 'string'],
            'is_use' => ['required', Rule::in([Coupon::STATUS_UNUSED, Coupon::STATUS_USE, '1', '2'])],
        ]);

        $couponIds = $this->couponActionService->parseCouponIds($payload['ids_text']);
        if (empty($couponIds)) {
            return redirect()
                ->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的优惠码 ID。'])
                ->withInput();
        }

        $isUse = (int) $payload['is_use'];
        $affected = $this->couponActionService->updateUseStatus($couponIds, $isUse);
        $usageLabel = Coupon::getStatusUseMap()[$isUse] ?? (string) $isUse;

        return redirect(admin_url('v2/coupon/batch-use?ids='.implode(',', $couponIds)))
            ->with('status', '已批量把 '.$affected.' 个优惠码的使用状态调整为 '.$usageLabel);
    }

    private function storeBatchDiscount(Request $request)
    {
        $payload = $request->validate([
            'ids_text' => ['required', 'string'],
            'discount' => ['required', 'numeric', 'min:0'],
        ]);

        $couponIds = $this->couponActionService->parseCouponIds($payload['ids_text']);
        if (empty($couponIds)) {
            return redirect()
                ->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的优惠码 ID。'])
                ->withInput();
        }

        $discount = (float) $payload['discount'];
        $affected = $this->couponActionService->updateDiscount($couponIds, $discount);

        return redirect(admin_url('v2/coupon/batch-discount?ids='.implode(',', $couponIds)))
            ->with('status', '已批量把 '.$affected.' 个优惠码的折扣调整为 '.$discount);
    }

    private function storeBatchCode(Request $request)
    {
        $payload = $request->validate([
            'ids_text' => ['required', 'string'],
            'prefix' => ['nullable', 'string', 'max:64'],
            'length' => ['required', 'integer', 'min:4', 'max:32'],
        ]);

        $couponIds = $this->couponActionService->parseCouponIds($payload['ids_text']);
        if (empty($couponIds)) {
            return redirect()
                ->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的优惠码 ID。'])
                ->withInput();
        }

        $affected = $this->couponActionService->regenerateCodes(
            $couponIds,
            $payload['prefix'] ?? null,
            (int) $payload['length']
        );

        return redirect(admin_url('v2/coupon/batch-code?ids='.implode(',', $couponIds)))
            ->with('status', '已批量重生成 '.$affected.' 个优惠码内容');
    }

    private function storeBatchCodePrefix(Request $request)
    {
        $payload = $request->validate([
            'ids_text' => ['required', 'string'],
            'prefix' => ['required', 'string', 'max:64'],
        ]);

        $couponIds = $this->couponActionService->parseCouponIds($payload['ids_text']);
        if (empty($couponIds)) {
            return redirect()
                ->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的优惠码 ID。'])
                ->withInput();
        }

        $affected = $this->couponActionService->addCodePrefix($couponIds, $payload['prefix']);

        return redirect(admin_url('v2/coupon/batch-code-prefix?ids='.implode(',', $couponIds)))
            ->with('status', '已批量为 '.$affected.' 个优惠码添加前缀');
    }

    private function storeBatchCodeSuffix(Request $request)
    {
        $payload = $request->validate([
            'ids_text' => ['required', 'string'],
            'suffix' => ['required', 'string', 'max:64'],
        ]);

        $couponIds = $this->couponActionService->parseCouponIds($payload['ids_text']);
        if (empty($couponIds)) {
            return redirect()
                ->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的优惠码 ID。'])
                ->withInput();
        }

        $affected = $this->couponActionService->addCodeSuffix($couponIds, $payload['suffix']);

        return redirect(admin_url('v2/coupon/batch-code-suffix?ids='.implode(',', $couponIds)))
            ->with('status', '已批量为 '.$affected.' 个优惠码添加后缀');
    }

    private function isBatchMode(Request $request): bool
    {
        return $request->query('mode') === 'batch' || $request->input('mode') === 'batch';
    }

    private function isBatchStatusMode(Request $request): bool
    {
        return $request->query('mode') === 'batch-status' || $request->input('mode') === 'batch-status';
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
