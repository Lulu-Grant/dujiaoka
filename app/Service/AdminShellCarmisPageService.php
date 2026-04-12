<?php

namespace App\Service;

use App\Models\Carmis;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellCarmisPageService extends AbstractAdminShellPageService
{
    /**
     * @var \App\Service\CatalogAdminPresenterService
     */
    private $catalogPresenter;

    public function __construct(AdminShellResourceRegistry $resourceRegistry, CatalogAdminPresenterService $catalogPresenter)
    {
        parent::__construct($resourceRegistry);
        $this->catalogPresenter = $catalogPresenter;
    }

    public function extractFilters(Request $request): array
    {
        return [
            'id' => $request->query('id'),
            'goods_id' => $request->query('goods_id'),
            'status' => $request->query('status'),
            'scope' => $request->query('scope'),
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Carmis::query()->with('goods:id,gd_name')->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['goods_id'])) {
            $query->where('goods_id', (int) $filters['goods_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', (int) $filters['status']);
        }

        return $query->paginate(15)->appends($filters);
    }

    public function find(int $id, ?string $scope = null)
    {
        $query = Carmis::query()->with('goods:id,gd_name');

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function buildTable(LengthAwarePaginator $carmis, array $filters): array
    {
        $scope = $filters['scope'] ?? null;
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '关联商品', '状态', '循环使用', '卡密内容', '更新时间', '操作'],
            'rows' => $carmis->getCollection()->map(function (Carmis $carmi) use ($scope, $definition) {
                return [
                    $carmi->id,
                    e(optional($carmi->goods)->gd_name ?: '未关联商品'),
                    $this->renderStatusCell($carmi),
                    e($this->catalogPresenter->loopLabel($carmi->is_loop) ?: '否'),
                    e(mb_strimwidth($carmi->carmi, 0, 24, '...')),
                    e((string) $carmi->updated_at),
                    $this->renderActionLinks([
                        [
                            'label' => '编辑卡密',
                            'href' => admin_url($definition['uri'].'/'.$carmi->id.'/edit'),
                        ],
                        [
                            'label' => '查看详情',
                            'href' => admin_url($definition['uri'].'/'.$carmi->id.($scope ? '?scope='.$scope : '')),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有卡密记录。',
            'empty_description' => '可以调整商品、状态或范围筛选条件，继续查找卡密。',
            'paginator' => $carmis,
        ];
    }

    public function buildHeader(LengthAwarePaginator $carmis): array
    {
        $header = $this->buildResourceHeader('共 '.$carmis->total().' 条卡密');
        $header['actions'][] = [
            'label' => '新建卡密',
            'href' => admin_url('v2/carmis/create'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '导入卡密',
            'href' => admin_url('v2/carmis/import'),
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
                ['label' => '商品 ID', 'name' => 'goods_id', 'type' => 'number', 'value' => $filters['goods_id'] ?? null],
                [
                    'label' => '状态',
                    'name' => 'status',
                    'type' => 'select',
                    'value' => $filters['status'] ?? null,
                    'options' => ['' => '全部'] + Carmis::getStatusMap(),
                ],
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

    public function buildShowHeader(?string $scope = null): array
    {
        return $this->buildResourceShowHeader($scope);
    }

    public function buildIndexPageData(LengthAwarePaginator $carmis, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($carmis),
            $this->buildFilters($filters),
            $this->buildTable($carmis, $filters)
        );
    }

    public function buildShowPageData($carmi, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader($scope),
            $this->detailItems($carmi)
        );
    }

    public function detailItems(Carmis $carmi): array
    {
        return [
            ['label' => 'ID', 'value' => $carmi->id],
            ['label' => '关联商品', 'value' => e(optional($carmi->goods)->gd_name ?: '未关联商品')],
            ['label' => '状态', 'value' => e($this->catalogPresenter->carmiStatusLabel($carmi->status))],
            ['label' => '循环使用', 'value' => e($this->catalogPresenter->loopLabel($carmi->is_loop) ?: '否')],
            [
                'label' => '卡密内容',
                'value' => e($carmi->carmi),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; font-size: 14px; font-weight: 500;',
            ],
            ['label' => '创建时间', 'value' => e((string) $carmi->created_at)],
            ['label' => '更新时间', 'value' => e((string) $carmi->updated_at)],
            ['label' => '删除状态', 'value' => $carmi->deleted_at ? '已删除' : '正常'],
        ];
    }

    protected function resourceKey(): string
    {
        return 'carmis';
    }

    private function renderStatusCell(Carmis $carmi): string
    {
        if ($carmi->deleted_at) {
            return '<span class="pill trashed">回收站</span>';
        }

        return sprintf(
            '<span class="pill %s">%s</span>',
            (int) $carmi->status === Carmis::STATUS_UNSOLD ? 'open' : 'closed',
            e($this->catalogPresenter->carmiStatusLabel($carmi->status))
        );
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
