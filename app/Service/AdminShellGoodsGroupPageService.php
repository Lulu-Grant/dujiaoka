<?php

namespace App\Service;

use App\Models\GoodsGroup;
use App\Service\Contracts\AdminShellPageServiceInterface;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellGoodsGroupPageService implements AdminShellPageServiceInterface
{
    /**
     * @var \App\Service\AdminStatusPresenterService
     */
    private $statusPresenter;

    public function __construct(AdminStatusPresenterService $statusPresenter)
    {
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

        return [
            'headers' => ['ID', '分类名称', '状态', '排序', '商品数', '创建时间', '更新时间', '操作'],
            'rows' => $groups->getCollection()->map(function (GoodsGroup $group) use ($scope) {
                return [
                    $group->id,
                    e($group->gp_name),
                    $this->renderStatusCell($group),
                    $group->ord,
                    $group->goods_count,
                    e((string) $group->created_at),
                    e((string) $group->updated_at),
                    $this->renderActionLinks([
                        [
                            'label' => '查看详情',
                            'href' => admin_url('v2/goods-group/'.$group->id.($scope ? '?scope='.$scope : '')),
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
        return [
            'title' => '商品分类管理',
            'description' => '这是第一批后台迁移样板页。当前使用普通 Laravel 控制器、服务和 Blade 渲染，不再依赖 Dcat Grid。',
            'meta' => '共 '.$groups->total().' 条记录',
            'actions' => [
                [
                    'label' => '迁移合同',
                    'href' => 'https://github.com/Lulu-Grant/dujiaoka/blob/master/docs/admin-first-batch-migration-contracts.md',
                    'variant' => 'secondary',
                ],
            ],
        ];
    }

    public function buildFilters(array $filters): array
    {
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
            'resetUrl' => admin_url('v2/goods-group'),
        ];
    }

    public function buildShowHeader(?string $scope = null): array
    {
        return [
            'title' => '商品分类详情',
            'description' => '这是商品分类页的详情样板。后续真正替换后台壳时，可以直接照着这组字段合同迁移。',
            'actions' => [
                ['label' => '返回列表', 'href' => admin_url('v2/goods-group'.($scope ? '?scope='.$scope : ''))],
            ],
        ];
    }

    public function buildIndexPageData(LengthAwarePaginator $groups, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            '商品分类管理 - 后台壳样板',
            $this->buildHeader($groups),
            $this->buildFilters($filters),
            $this->buildTable($groups, $filters)
        );
    }

    public function buildShowPageData($group, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            '商品分类详情 - 后台壳样板',
            $this->buildShowHeader($scope),
            $this->detailItems($group)
        );
    }

    public function detailItems(GoodsGroup $group): array
    {
        return [
            ['label' => 'ID', 'value' => $group->id],
            ['label' => '分类名称', 'value' => e($group->gp_name)],
            ['label' => '状态', 'value' => strip_tags($this->statusPresenter->openStatusLabel($group->is_open))],
            ['label' => '排序', 'value' => $group->ord],
            ['label' => '商品数', 'value' => $group->goods_count],
            ['label' => '创建时间', 'value' => e((string) $group->created_at)],
            ['label' => '更新时间', 'value' => e((string) $group->updated_at)],
            ['label' => '删除状态', 'value' => $group->deleted_at ? '已删除' : '正常'],
        ];
    }

    private function renderStatusCell(GoodsGroup $group): string
    {
        if ($group->deleted_at) {
            return '<span class="pill trashed">回收站</span>';
        }

        return sprintf(
            '<span class="pill %s">%s</span>',
            (int) $group->is_open ? 'open' : 'closed',
            e(strip_tags($this->statusPresenter->openStatusLabel($group->is_open)))
        );
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
