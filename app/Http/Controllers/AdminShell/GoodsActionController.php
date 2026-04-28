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
        if ($this->isBatchKeywordsSuffixMode($request)) {
            return $this->renderBatchKeywordsSuffixPage($request);
        }

        if ($this->isBatchKeywordsMode($request)) {
            return $this->renderBatchKeywordsPage($request);
        }

        if ($this->isBatchDescriptionMode($request)) {
            return $this->renderBatchDescriptionPage($request);
        }

        if ($this->isBatchBuyPromptMode($request)) {
            return $this->renderBatchBuyPromptPage($request);
        }

        if ($this->isBatchOrdMode($request)) {
            return $this->renderBatchOrdPage($request);
        }

        if ($this->isBatchSalesVolumeMode($request)) {
            return $this->renderBatchSalesVolumePage($request);
        }

        if ($this->isBatchBuyLimitMode($request)) {
            return $this->renderBatchBuyLimitPage($request);
        }

        if ($this->isBatchGroupMode($request)) {
            return $this->renderBatchGroupPage($request);
        }

        $cloneSource = null;
        if ($request->filled('clone') && ctype_digit((string) $request->query('clone'))) {
            $cloneSource = Goods::query()->with(['group:id,gp_name', 'coupon:id,coupon'])->find((int) $request->query('clone'));
        }

        $defaults = $cloneSource
            ? $this->goodsActionService->cloneDefaults($cloneSource)
            : $this->goodsActionService->createDefaults();

        $actions = [
            ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
            ['label' => '批量设置限购数量', 'href' => admin_url('v2/goods/create').'?mode=batch-buy-limit-num', 'variant' => 'secondary'],
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
        if ($this->isBatchKeywordsSuffixMode($request)) {
            return $this->submitBatchKeywordsSuffix($request);
        }

        if ($this->isBatchKeywordsMode($request)) {
            return $this->submitBatchKeywords($request);
        }

        if ($this->isBatchDescriptionMode($request)) {
            return $this->submitBatchDescription($request);
        }

        if ($this->isBatchBuyPromptMode($request)) {
            return $this->submitBatchBuyPrompt($request);
        }

        if ($this->isBatchOrdMode($request)) {
            return $this->submitBatchOrd($request);
        }

        if ($this->isBatchSalesVolumeMode($request)) {
            return $this->submitBatchSalesVolume($request);
        }

        if ($this->isBatchBuyLimitMode($request)) {
            return $this->submitBatchBuyLimit($request);
        }

        if ($this->isBatchGroupMode($request)) {
            return $this->submitBatchGroup($request);
        }

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

    private function isBatchBuyLimitMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-buy-limit-num';
    }

    private function isBatchSalesVolumeMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-sales-volume';
    }

    private function isBatchOrdMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-ord';
    }

    private function isBatchGroupMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-group';
    }

    private function isBatchBuyPromptMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-buy-prompt';
    }

    private function isBatchDescriptionMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-description';
    }

    private function isBatchKeywordsMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-keywords';
    }

    private function isBatchKeywordsSuffixMode(Request $request): bool
    {
        return (string) $request->query('mode', $request->input('mode', '')) === 'batch-keywords-suffix';
    }

    private function renderBatchBuyLimitPage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchBuyLimitDefaults($goodsIds);

        return view('admin-shell.goods.batch-buy-limit-num', [
            'title' => '批量设置限购数量 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置限购数量',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品限购数量的统一调整，不触碰价格、库存、分类和其他复杂字段。',
                'meta' => '适合活动期统一收紧购买数量或恢复默认限购。输入商品 ID 即可执行，支持换行、逗号和空格混输。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量启停商品', 'href' => admin_url('v2/goods/batch-status'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-buy-limit-num',
            'submitLabel' => '执行限购更新',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchBuyLimitContext($goodsIds),
        ]);
    }

    private function renderBatchSalesVolumePage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchSalesVolumeDefaults($goodsIds);

        return view('admin-shell.goods.batch-sales-volume', [
            'title' => '批量设置销量 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置销量',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品销量的统一调整，不触碰价格、库存、分类和商品类型。',
                'meta' => '适合活动期修正展示销量、导入历史销量或做人工运营纠偏。输入商品 ID 即可执行，支持换行、逗号和空格混输。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量设置限购数量', 'href' => admin_url('v2/goods/create').'?mode=batch-buy-limit-num', 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-sales-volume',
            'submitLabel' => '执行销量更新',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchSalesVolumeContext($goodsIds),
        ]);
    }

    private function renderBatchOrdPage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchOrdDefaults($goodsIds);

        return view('admin-shell.goods.batch-ord', [
            'title' => '批量设置排序 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置排序',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品排序值的统一调整，不触碰价格、库存、分类、商品类型和启用状态。',
                'meta' => '适合活动编排、首页排序整理或批量归档前后调整展示顺序。输入商品 ID 即可执行，支持换行、逗号和空格混输。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量设置销量', 'href' => admin_url('v2/goods/create').'?mode=batch-sales-volume', 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-ord',
            'submitLabel' => '执行排序更新',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchOrdContext($goodsIds),
        ]);
    }

    private function renderBatchBuyPromptPage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchBuyPromptDefaults($goodsIds);

        return view('admin-shell.goods.batch-buy-prompt', [
            'title' => '批量设置购买提示 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置购买提示',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品购买提示的统一调整，不触碰价格、库存、分类、商品类型和启用状态。',
                'meta' => '适合活动前统一补充购买说明、交付提醒或售后提示。输入商品 ID 即可执行，支持换行、逗号和空格混输。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量设置排序', 'href' => admin_url('v2/goods/create').'?mode=batch-ord', 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-buy-prompt',
            'submitLabel' => '执行购买提示更新',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchBuyPromptContext($goodsIds),
        ]);
    }

    private function renderBatchDescriptionPage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchDescriptionDefaults($goodsIds);

        return view('admin-shell.goods.batch-description', [
            'title' => '批量设置商品说明 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置商品说明',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品说明的统一调整，不触碰价格、库存、分类、商品类型、销量、排序和启用状态。',
                'meta' => '适合活动期统一补充商品说明、售后须知或交付细节。输入商品 ID 即可执行，支持换行、逗号和空格混输。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量设置购买提示', 'href' => admin_url('v2/goods/create').'?mode=batch-buy-prompt', 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-description',
            'submitLabel' => '执行商品说明更新',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchDescriptionContext($goodsIds),
        ]);
    }

    private function renderBatchKeywordsPage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchKeywordsDefaults($goodsIds);

        return view('admin-shell.goods.batch-keywords', [
            'title' => '批量设置商品关键字 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量设置商品关键字',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品关键字的统一调整，不触碰价格、库存、分类、商品类型、销量、排序和启用状态。',
                'meta' => '适合活动期统一补充检索词、后台识别标签或 SEO 关键字。输入商品 ID 即可执行，支持换行、逗号和空格混输。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量设置商品说明', 'href' => admin_url('v2/goods/create').'?mode=batch-description', 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-keywords',
            'submitLabel' => '执行商品关键字更新',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchKeywordsContext($goodsIds),
        ]);
    }

    private function renderBatchKeywordsSuffixPage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchKeywordsSuffixDefaults($goodsIds);

        return view('admin-shell.goods.batch-keywords-suffix', [
            'title' => '批量添加商品关键字后缀 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量添加商品关键字后缀',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品关键字尾部标签追加，不触碰价格、库存、分类、商品类型、销量、排序和启用状态。',
                'meta' => '适合活动期给已有关键字追加检索标签或运营标记。提交后只把目标后缀追加到当前商品关键字末尾。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量设置关键字', 'href' => admin_url('v2/goods/create').'?mode=batch-keywords', 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-keywords-suffix',
            'submitLabel' => '执行关键字后缀追加',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchKeywordsContext($goodsIds),
        ]);
    }

    private function submitBatchBuyLimit(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'buy_limit_num' => ['required', 'integer', 'min:0'],
            'mode' => ['nullable', 'string'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->updateBuyLimitNum($goodsIds, (int) $validated['buy_limit_num']);

        return redirect(admin_url('v2/goods/create').'?mode=batch-buy-limit-num&ids='.implode(',', $goodsIds))
            ->with('status', '已批量设置 '.$affected.' 个商品的限购数量');
    }

    private function submitBatchSalesVolume(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'sales_volume' => ['required', 'integer', 'min:0'],
            'mode' => ['nullable', 'string'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->updateSalesVolume($goodsIds, (int) $validated['sales_volume']);

        return redirect(admin_url('v2/goods/create').'?mode=batch-sales-volume&ids='.implode(',', $goodsIds))
            ->with('status', '已批量设置 '.$affected.' 个商品的销量');
    }

    private function submitBatchOrd(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'ord' => ['required', 'integer', 'min:0'],
            'mode' => ['nullable', 'string'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->updateOrd($goodsIds, (int) $validated['ord']);

        return redirect(admin_url('v2/goods/create').'?mode=batch-ord&ids='.implode(',', $goodsIds))
            ->with('status', '已批量设置 '.$affected.' 个商品的排序');
    }

    private function submitBatchBuyPrompt(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'buy_prompt' => ['nullable', 'string'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->updateBuyPrompt($goodsIds, (string) ($validated['buy_prompt'] ?? ''));

        return redirect(admin_url('v2/goods/create').'?mode=batch-buy-prompt&ids='.implode(',', $goodsIds))
            ->with('status', '已批量设置 '.$affected.' 个商品的购买提示');
    }

    private function submitBatchDescription(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->updateDescription($goodsIds, (string) ($validated['description'] ?? ''));

        return redirect(admin_url('v2/goods/create').'?mode=batch-description&ids='.implode(',', $goodsIds))
            ->with('status', '已批量设置 '.$affected.' 个商品的商品说明');
    }

    private function submitBatchKeywords(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'gd_keywords' => ['required', 'string', 'max:255'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->updateKeywords($goodsIds, $validated['gd_keywords']);

        return redirect(admin_url('v2/goods/create').'?mode=batch-keywords&ids='.implode(',', $goodsIds))
            ->with('status', '已批量设置 '.$affected.' 个商品的商品关键字');
    }

    private function submitBatchKeywordsSuffix(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'keywords_suffix' => ['required', 'string', 'max:100'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $suffix = trim($validated['keywords_suffix']);
        if ($suffix === '') {
            return redirect()->back()
                ->withErrors(['keywords_suffix' => '请填写需要追加的商品关键字后缀。'])
                ->withInput();
        }

        $affected = $this->goodsActionService->addKeywordsSuffix($goodsIds, $suffix);

        return redirect(admin_url('v2/goods/create').'?mode=batch-keywords-suffix&ids='.implode(',', $goodsIds))
            ->with('status', '已批量为 '.$affected.' 个商品添加关键字后缀');
    }

    private function renderBatchGroupPage(Request $request)
    {
        $goodsIds = $this->goodsActionService->parseGoodsIds((string) $request->query('ids', ''));
        $defaults = $this->goodsActionService->batchGroupDefaults($goodsIds);

        return view('admin-shell.goods.batch-group', [
            'title' => '批量切换商品分类 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Batch',
                'title' => '批量切换商品分类',
                'description' => '这是后台壳中的低风险批量动作页。当前只承接商品分类迁移，不触碰价格、库存、限购和启用状态。',
                'meta' => '适合活动归档、运营重组或把一批商品统一归到新分类下。提交后只更新分类字段。',
                'actions' => [
                    ['label' => '返回商品概览', 'href' => admin_url('v2/goods')],
                    ['label' => '批量启停商品', 'href' => admin_url('v2/goods/batch-status'), 'variant' => 'secondary'],
                ],
            ],
            'formAction' => admin_url('v2/goods/create').'?mode=batch-group',
            'submitLabel' => '执行分类迁移',
            'defaults' => $defaults,
            'context' => $this->goodsActionService->batchGroupContext($goodsIds),
            'groupOptions' => $this->adminSelectOptionService->goodsGroupOptions(),
        ]);
    }

    private function submitBatchGroup(Request $request)
    {
        $validated = $request->validate([
            'ids_text' => ['required', 'string'],
            'group_id' => ['required', 'integer', 'exists:goods_group,id'],
            'mode' => ['nullable', 'string'],
        ]);

        $goodsIds = $this->goodsActionService->parseGoodsIds($validated['ids_text']);
        if (empty($goodsIds)) {
            return redirect()->back()
                ->withErrors(['ids_text' => '请至少填写一个有效的商品 ID。'])
                ->withInput();
        }

        $targetGroupId = (int) $validated['group_id'];
        $affected = $this->goodsActionService->updateGroupId($goodsIds, $targetGroupId);
        $groupName = $this->adminSelectOptionService->goodsGroupOptions()[$targetGroupId] ?? (string) $targetGroupId;

        return redirect(admin_url('v2/goods/create').'?mode=batch-group&ids='.implode(',', $goodsIds))
            ->with('status', '已批量把 '.$affected.' 个商品切换到分类 '.$groupName);
    }
}
