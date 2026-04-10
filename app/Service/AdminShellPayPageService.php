<?php

namespace App\Service;

use App\Models\Pay;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminShellPayPageService
{
    /**
     * @var \App\Service\PayAdminPresenterService
     */
    private $presenter;

    public function __construct(PayAdminPresenterService $presenter)
    {
        $this->presenter = $presenter;
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Pay::query()->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['pay_check'])) {
            $query->where('pay_check', $filters['pay_check']);
        }

        if (!empty($filters['pay_name'])) {
            $query->where('pay_name', 'like', '%'.$filters['pay_name'].'%');
        }

        return $query->paginate(15)->appends($filters);
    }

    public function find(int $id, ?string $scope = null): Pay
    {
        $query = Pay::query();

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function buildTable(LengthAwarePaginator $pays, array $filters): array
    {
        $scope = $filters['scope'] ?? null;

        return [
            'headers' => ['ID', '支付名称', '支付标识', '生命周期', '支付方式', '支付场景', '启用状态', '更新时间', '操作'],
            'rows' => $pays->getCollection()->map(function (Pay $pay) use ($scope) {
                return [
                    $pay->id,
                    e($pay->pay_name),
                    e($pay->pay_check),
                    $this->presenter->lifecycleBadge($pay->lifecycle),
                    e($this->presenter->methodLabel($pay->pay_method)),
                    e($this->presenter->clientLabel($pay->pay_client)),
                    $this->renderStatusCell($pay),
                    e((string) $pay->updated_at),
                    $this->renderActionLinks([
                        [
                            'label' => '查看详情',
                            'href' => admin_url('v2/pay/'.$pay->id.($scope ? '?scope='.$scope : '')),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有支付通道记录。',
            'empty_description' => '可以调整支付名称、支付标识或范围筛选条件，继续查找通道。',
            'paginator' => $pays,
        ];
    }

    public function buildHeader(LengthAwarePaginator $pays): array
    {
        return [
            'title' => '支付通道管理',
            'description' => '这是第一批后台迁移的第三张样板页。支付通道的生命周期、支付方式、支付场景都直接复用现有 presenter 与模型映射。',
            'meta' => '共 '.$pays->total().' 条通道',
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
                ['label' => '支付标识', 'name' => 'pay_check', 'value' => $filters['pay_check'] ?? null],
                ['label' => '支付名称', 'name' => 'pay_name', 'value' => $filters['pay_name'] ?? null],
                [
                    'label' => '范围',
                    'name' => 'scope',
                    'type' => 'select',
                    'value' => $filters['scope'] ?? null,
                    'options' => ['' => '全部', 'trashed' => '回收站'],
                ],
            ],
            'resetUrl' => admin_url('v2/pay'),
        ];
    }

    public function buildShowHeader(?string $scope = null): array
    {
        return [
            'title' => '支付通道详情',
            'description' => '这张详情页固定了支付通道的展示合同，后续迁移编辑页时可以直接在这套壳上扩展。',
            'actions' => [
                ['label' => '返回列表', 'href' => admin_url('v2/pay'.($scope ? '?scope='.$scope : ''))],
            ],
        ];
    }

    public function buildIndexPageData(LengthAwarePaginator $pays, array $filters): array
    {
        return [
            'title' => '支付通道管理 - 后台壳样板',
            'header' => $this->buildHeader($pays),
            'filterPanel' => $this->buildFilters($filters),
            'table' => $this->buildTable($pays, $filters),
        ];
    }

    public function buildShowPageData(Pay $pay, ?string $scope = null): array
    {
        return [
            'title' => '支付通道详情 - 后台壳样板',
            'header' => $this->buildShowHeader($scope),
            'items' => $this->detailItems($pay),
        ];
    }

    public function detailItems(Pay $pay): array
    {
        return [
            ['label' => 'ID', 'value' => $pay->id],
            ['label' => '支付名称', 'value' => e($pay->pay_name)],
            ['label' => '支付标识', 'value' => e($pay->pay_check)],
            ['label' => '生命周期', 'value' => e($this->presenter->lifecycleLabel($pay->lifecycle))],
            ['label' => '支付场景', 'value' => e($this->presenter->clientLabel($pay->pay_client))],
            ['label' => '支付方式', 'value' => e($this->presenter->methodLabel($pay->pay_method))],
            ['label' => '启用状态', 'value' => e(strip_tags($this->presenter->openStatusLabel($pay->is_open)))],
            ['label' => '支付路由', 'value' => e($pay->pay_handleroute)],
            ['label' => '商户 ID', 'value' => e($pay->merchant_id)],
            ['label' => '商户 KEY', 'value' => e($pay->merchant_key)],
            ['label' => '商户密钥', 'value' => e($pay->merchant_pem)],
            ['label' => '创建时间', 'value' => e((string) $pay->created_at)],
            ['label' => '更新时间', 'value' => e((string) $pay->updated_at)],
        ];
    }

    private function renderStatusCell(Pay $pay): string
    {
        if ($pay->deleted_at) {
            return '<span class="pill trashed">回收站</span>';
        }

        return sprintf(
            '<span class="pill %s">%s</span>',
            (int) $pay->is_open ? 'open' : 'closed',
            e(strip_tags($this->presenter->openStatusLabel($pay->is_open)))
        );
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
