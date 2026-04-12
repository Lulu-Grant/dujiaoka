<?php

namespace App\Service;

use App\Models\GoodsGroup;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellGoodsGroupPageService extends AbstractAdminShellPageService
{
    /**
     * @var \App\Service\AdminStatusPresenterService
     */
    private $statusPresenter;

    public function __construct(AdminShellResourceRegistry $resourceRegistry, AdminStatusPresenterService $statusPresenter)
    {
        parent::__construct($resourceRegistry);
        $this->statusPresenter = $statusPresenter;
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = GoodsGroup::query()->withCount('goods')->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        return $query->paginate(15)->appends($filters);
    }

    public function extractFilters(Request $request): array
    {
        return [
            'id' => $request->query('id'),
            'scope' => $request->query('scope'),
        ];
    }

    public function find(int $id, ?string $scope = null): GoodsGroup
    {
        $query = GoodsGroup::query()->withCount('goods');

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function buildTable(LengthAwarePaginator $groups, array $filters): array
    {
        $scope = $filters['scope'] ?? null;
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '分类名称', '状态', '排序', '商品数', '创建时间', '更新时间', '操作'],
            'rows' => $groups->getCollection()->map(function (GoodsGroup $group) use ($scope, $definition) {
                return [
                    $group->id,
                    e($group->gp_name),
                    $this->renderStatusCell($group),
                    $this->renderSortCell($group),
                    $this->renderGoodsCountCell($group),
                    e((string) $group->created_at),
                    e((string) $group->updated_at),
                    $this->renderActionLinks([
                        [
                            'label' => '编辑分类',
                            'href' => admin_url($definition['uri'].'/'.$group->id.'/edit'),
                        ],
                        [
                            'label' => '查看详情',
                            'href' => admin_url($definition['uri'].'/'.$group->id.($scope ? '?scope='.$scope : '')),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有商品分类记录。',
            'empty_description' => '可以调整筛选条件，或切换范围查看回收站中的分类。',
            'paginator' => $groups,
        ];
    }

    public function buildHeader(LengthAwarePaginator $groups): array
    {
        $header = $this->buildResourceHeader('共 '.$groups->total().' 条记录');
        $header['actions'][] = [
            'label' => '新建商品分类',
            'href' => admin_url('v2/goods-group/create'),
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

    public function buildShowHeader(?string $scope = null, ?GoodsGroup $group = null): array
    {
        $header = $this->buildResourceShowHeader($scope);

        if ($group && !$group->deleted_at) {
            $header['actions'][] = [
                'label' => '编辑分类',
                'href' => admin_url('v2/goods-group/'.$group->id.'/edit'),
                'variant' => 'secondary',
            ];
        }

        return $header;
    }

    public function buildIndexPageData(LengthAwarePaginator $groups, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($groups),
            $this->buildFilters($filters),
            $this->buildTable($groups, $filters)
        );
    }

    public function buildShowPageData($group, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader($scope, $group),
            $this->detailItems($group)
        );
    }

    public function detailItems(GoodsGroup $group): array
    {
        $statusLabel = strip_tags($this->statusPresenter->openStatusLabel($group->is_open));
        $statusHint = (int) $group->is_open ? '前台可选' : '前台隐藏';
        $goodsCount = (int) $group->goods_count;

        return [
            ['label' => 'ID', 'value' => $group->id],
            ['label' => '分类名称', 'value' => e($group->gp_name)],
            [
                'label' => '状态',
                'value' => sprintf(
                    '<span class="pill %s">%s</span><span style="margin-left: 10px; color: #66756b;">%s</span>',
                    (int) $group->is_open ? 'open' : 'closed',
                    e($statusLabel),
                    e($statusHint)
                ),
            ],
            [
                'label' => '排序',
                'value' => sprintf(
                    '<strong>%d</strong><span style="margin-left: 10px; color: #66756b;">数字越小越靠前</span>',
                    (int) $group->ord
                ),
            ],
            [
                'label' => '商品数',
                'value' => sprintf(
                    '<strong>%d</strong><span style="margin-left: 10px; color: #66756b;">%s</span>',
                    $goodsCount,
                    e($goodsCount > 0 ? '已有商品挂载' : '当前未关联商品')
                ),
            ],
            ['label' => '创建时间', 'value' => e((string) $group->created_at)],
            ['label' => '更新时间', 'value' => e((string) $group->updated_at)],
            [
                'label' => '删除状态',
                'value' => $group->deleted_at
                    ? '<span class="pill trashed">已删除</span><span style="margin-left: 10px; color: #66756b;">仅回收站可见</span>'
                    : '<span class="pill open">正常</span><span style="margin-left: 10px; color: #66756b;">可继续维护</span>',
            ],
        ];
    }

    private function renderStatusCell(GoodsGroup $group): string
    {
        if ($group->deleted_at) {
            return '<span class="pill trashed">回收站</span><span style="margin-left: 10px; color: #66756b;">不可在前台使用</span>';
        }

        return sprintf(
            '<span class="pill %s">%s</span><span style="margin-left: 10px; color: #66756b;">%s</span>',
            (int) $group->is_open ? 'open' : 'closed',
            e(strip_tags($this->statusPresenter->openStatusLabel($group->is_open))),
            e((int) $group->is_open ? '前台可选' : '前台隐藏')
        );
    }

    private function renderSortCell(GoodsGroup $group): string
    {
        return sprintf(
            '<strong>%d</strong><span style="margin-left: 10px; color: #66756b;">越小越靠前</span>',
            (int) $group->ord
        );
    }

    private function renderGoodsCountCell(GoodsGroup $group): string
    {
        $goodsCount = (int) $group->goods_count;

        return sprintf(
            '<strong>%d</strong><span style="margin-left: 10px; color: #66756b;">%s</span>',
            $goodsCount,
            e($goodsCount > 0 ? '已有商品挂载' : '当前未关联商品')
        );
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }

    protected function resourceKey(): string
    {
        return 'goods-group';
    }
}
