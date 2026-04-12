<?php

namespace App\Service;

use App\Models\Goods;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellGoodsPageService extends AbstractAdminShellPageService
{
    /**
     * @var \App\Service\CatalogAdminPresenterService
     */
    private $catalogPresenter;

    /**
     * @var \App\Service\AdminStatusPresenterService
     */
    private $statusPresenter;

    /**
     * @var \App\Service\GoodsActionService
     */
    private $goodsActionService;

    public function __construct(
        AdminShellResourceRegistry $resourceRegistry,
        CatalogAdminPresenterService $catalogPresenter,
        AdminStatusPresenterService $statusPresenter,
        GoodsActionService $goodsActionService
    ) {
        parent::__construct($resourceRegistry);
        $this->catalogPresenter = $catalogPresenter;
        $this->statusPresenter = $statusPresenter;
        $this->goodsActionService = $goodsActionService;
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Goods::query()
            ->with(['group:id,gp_name', 'coupon:id,coupon'])
            ->withCount(['carmis as carmis_count' => function ($builder) {
                $builder->where('status', \App\Models\Carmis::STATUS_UNSOLD);
            }])
            ->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['gd_name'])) {
            $query->where('gd_name', 'like', '%'.$filters['gd_name'].'%');
        }

        if (!empty($filters['type'])) {
            $query->where('type', (int) $filters['type']);
        }

        if (!empty($filters['group_id'])) {
            $query->where('group_id', (int) $filters['group_id']);
        }

        return $query->paginate(15)->appends($filters);
    }

    public function extractFilters(Request $request): array
    {
        return [
            'id' => $request->query('id'),
            'gd_name' => $request->query('gd_name'),
            'type' => $request->query('type'),
            'group_id' => $request->query('group_id'),
            'scope' => $request->query('scope'),
        ];
    }

    public function find(int $id, ?string $scope = null): Goods
    {
        $query = Goods::query()
            ->with(['group:id,gp_name', 'coupon:id,coupon'])
            ->withCount(['carmis as carmis_count' => function ($builder) {
                $builder->where('status', \App\Models\Carmis::STATUS_UNSOLD);
            }]);

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function buildTable(LengthAwarePaginator $goods, array $filters): array
    {
        $scope = $filters['scope'] ?? null;
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '商品名称', '分类', '类型', '售价', '库存', '启用状态', '更新时间', '操作'],
            'rows' => $goods->getCollection()->map(function (Goods $item) use ($scope, $definition) {
                return [
                    $item->id,
                    e($item->gd_name),
                    e(optional($item->group)->gp_name ?: '未分类'),
                    e($this->catalogPresenter->goodsTypeLabel($item->type)),
                    e((string) $item->actual_price),
                    e((string) $item->in_stock),
                    $this->renderStatusCell($item),
                    e((string) $item->updated_at),
                    $this->renderActionLinks([
                        [
                            'label' => '编辑商品',
                            'href' => admin_url($definition['uri'].'/'.$item->id.'/edit'),
                        ],
                        [
                            'label' => '查看详情',
                            'href' => admin_url($definition['uri'].'/'.$item->id.($scope ? '?scope='.$scope : '')),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有商品记录。',
            'empty_description' => '可以调整商品名称、分类、类型或范围筛选条件，继续查找商品。',
            'paginator' => $goods,
        ];
    }

    public function buildHeader(LengthAwarePaginator $goods): array
    {
        $header = $this->buildResourceHeader('共 '.$goods->total().' 条商品');
        $header['actions'][] = [
            'label' => '新建商品',
            'href' => admin_url('v2/goods/create'),
            'variant' => 'primary',
        ];

        return $header;
    }

    public function buildFilters(array $filters): array
    {
        $definition = $this->resourceDefinition();

        return [
            'fields' => [
                ['label' => 'ID', 'name' => 'id', 'type' => 'number', 'value' => $filters['id'] ?? null],
                ['label' => '商品名称', 'name' => 'gd_name', 'value' => $filters['gd_name'] ?? null],
                [
                    'label' => '商品类型',
                    'name' => 'type',
                    'type' => 'select',
                    'value' => $filters['type'] ?? null,
                    'options' => ['' => '全部'] + Goods::getGoodsTypeMap(),
                ],
                ['label' => '分类 ID', 'name' => 'group_id', 'type' => 'number', 'value' => $filters['group_id'] ?? null],
                [
                    'label' => '范围',
                    'name' => 'scope',
                    'type' => 'select',
                    'value' => $filters['scope'] ?? null,
                    'options' => ['' => '全部', 'trashed' => '回收站'],
                ],
            ],
            'resetUrl' => admin_url($definition['uri']),
        ];
    }

    public function buildShowHeader(?string $scope = null, ?Goods $goods = null): array
    {
        $header = $this->buildResourceShowHeader($scope);

        if ($goods) {
            $header['actions'][] = [
                'label' => '编辑商品',
                'href' => admin_url('v2/goods/'.$goods->id.'/edit'),
                'variant' => 'secondary',
            ];
        }

        return $header;
    }

    public function buildIndexPageData(LengthAwarePaginator $goods, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($goods),
            $this->buildFilters($filters),
            $this->buildTable($goods, $filters)
        );
    }

    public function buildShowPageData($goods, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader($scope),
            $this->detailItems($goods)
        );
    }

    public function buildShowViewData(Goods $goods, ?string $scope = null): array
    {
        return [
            'title' => $this->buildDocumentTitle('show_title'),
            'header' => $this->buildShowHeader($scope, $goods),
            'summaryCards' => $this->goodsActionService->buildShowSummaryCards($goods),
            'sections' => $this->goodsActionService->buildShowSections($goods),
        ];
    }

    public function detailItems(Goods $goods): array
    {
        return [
            ['label' => 'ID', 'value' => $goods->id],
            ['label' => '商品名称', 'value' => e($goods->gd_name)],
            ['label' => '商品简介', 'value' => e($goods->gd_description)],
            ['label' => '商品关键字', 'value' => e($goods->gd_keywords)],
            ['label' => '所属分类', 'value' => e(optional($goods->group)->gp_name ?: '未分类')],
            ['label' => '商品类型', 'value' => e($this->catalogPresenter->goodsTypeLabel($goods->type))],
            ['label' => '原价', 'value' => e((string) $goods->retail_price)],
            ['label' => '售价', 'value' => e((string) $goods->actual_price)],
            ['label' => '库存', 'value' => e((string) $goods->in_stock)],
            ['label' => '销量', 'value' => e((string) $goods->sales_volume)],
            ['label' => '启用状态', 'value' => e(strip_tags($this->statusPresenter->openStatusLabel($goods->is_open)))],
            ['label' => '排序', 'value' => e((string) $goods->ord)],
            ['label' => '限购数量', 'value' => e((string) $goods->buy_limit_num)],
            ['label' => '关联优惠码', 'value' => e($goods->coupon->pluck('coupon')->implode(' / ') ?: '未关联优惠码')],
            [
                'label' => '购买提示',
                'value' => e((string) $goods->buy_prompt),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap;',
            ],
            [
                'label' => '商品说明',
                'value' => e((string) $goods->description),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap;',
            ],
            [
                'label' => '更多输入配置',
                'value' => e((string) $goods->other_ipu_cnf),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap;',
            ],
            [
                'label' => '批发价配置',
                'value' => e((string) $goods->wholesale_price_cnf),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap;',
            ],
            [
                'label' => 'API Hook',
                'value' => e((string) $goods->api_hook),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap;',
            ],
        ];
    }

    protected function resourceKey(): string
    {
        return 'goods';
    }

    private function renderStatusCell(Goods $goods): string
    {
        if ($goods->deleted_at) {
            return '<span class="pill trashed">回收站</span>';
        }

        return sprintf(
            '<span class="pill %s">%s</span>',
            (int) $goods->is_open ? 'open' : 'closed',
            e(strip_tags($this->statusPresenter->openStatusLabel($goods->is_open)))
        );
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
