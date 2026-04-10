<?php

namespace App\Service;

use App\Models\GoodsGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminShellGoodsGroupPageService
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
                    sprintf(
                        '<a href="%s">查看详情</a>',
                        e(admin_url('v2/goods-group/'.$group->id.($scope ? '?scope='.$scope : '')))
                    ),
                ];
            })->all(),
            'empty' => '当前条件下没有商品分类记录。',
            'paginator' => $groups,
        ];
    }

    public function buildHeader(LengthAwarePaginator $groups): array
    {
        return [
            'title' => '商品分类管理',
            'description' => '这是第一批后台迁移样板页。当前使用普通 Laravel 控制器、服务和 Blade 渲染，不再依赖 Dcat Grid。',
            'meta' => '共 '.$groups->total().' 条记录',
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
}
