<?php

namespace App\Service;

use App\Models\Pay;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellPayPageService extends AbstractAdminShellPageService
{
    /**
     * @var \App\Service\PayAdminPresenterService
     */
    private $presenter;

    public function __construct(AdminShellResourceRegistry $resourceRegistry, PayAdminPresenterService $presenter)
    {
        parent::__construct($resourceRegistry);
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

    public function extractFilters(Request $request): array
    {
        return [
            'id' => $request->query('id'),
            'pay_check' => $request->query('pay_check'),
            'pay_name' => $request->query('pay_name'),
            'scope' => $request->query('scope'),
        ];
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
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '支付名称', '支付标识', '生命周期', '支付方式', '支付场景', '启用状态', '更新时间', '操作'],
            'rows' => $pays->getCollection()->map(function (Pay $pay) use ($scope, $definition) {
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
                        'label' => '编辑通道',
                        'href' => admin_url($definition['uri'].'/'.$pay->id.'/edit'),
                    ],
                    [
                        'label' => '复制通道',
                        'href' => admin_url($definition['uri'].'/create?copy='.$pay->id),
                    ],
                    [
                        'label' => '查看详情',
                        'href' => admin_url($definition['uri'].'/'.$pay->id.($scope ? '?scope='.$scope : '')),
                    ],
                ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有支付通道记录。',
            'empty_description' => '可以调整支付名称、支付标识、生命周期或范围筛选条件，继续查找通道。',
            'paginator' => $pays,
        ];
    }

    public function buildHeader(LengthAwarePaginator $pays): array
    {
        $header = $this->buildResourceHeader('共 '.$pays->total().' 条通道');
        $header['actions'][] = [
            'label' => '批量启停通道',
            'href' => admin_url('v2/pay/batch-status'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '新建支付通道',
            'href' => admin_url('v2/pay/create'),
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
            'resetUrl' => admin_url($definition['uri']),
        ];
    }

    public function buildShowHeader(?string $scope = null, ?Pay $pay = null): array
    {
        $header = $this->buildResourceShowHeader($scope);

        if ($pay) {
            $header['meta'] = '支付标识：'.$pay->pay_check
                .' · 生命周期：'.$this->presenter->lifecycleLabel($pay->lifecycle)
                .' · 密钥字段已脱敏';
            $header['actions'][] = [
                'label' => '编辑通道',
                'href' => admin_url($this->resourceDefinition()['uri'].'/'.$pay->id.'/edit'),
                'variant' => 'primary',
            ];
            $header['actions'][] = [
                'label' => '复制通道',
                'href' => admin_url($this->resourceDefinition()['uri'].'/create?copy='.$pay->id),
                'variant' => 'secondary',
            ];
        }

        return $header;
    }

    public function buildIndexPageData(LengthAwarePaginator $pays, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($pays),
            $this->buildFilters($filters),
            $this->buildTable($pays, $filters)
        );
    }

    public function buildShowPageData($pay, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader($scope, $pay),
            $this->detailItems($pay)
        );
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
            ['label' => '安全状态', 'value' => $this->renderSecurityState($pay)],
            ['label' => '支付路由', 'value' => e($pay->pay_handleroute)],
            ['label' => '商户 ID', 'value' => e($pay->merchant_id)],
            ['label' => '商户 KEY', 'value' => $this->renderSecretValue($pay->merchant_key)],
            ['label' => '商户密钥', 'value' => $this->renderSecretValue($pay->merchant_pem)],
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

    private function renderSecurityState(Pay $pay): string
    {
        $items = [];

        $items[] = empty($pay->merchant_key) ? '商户 KEY 未配置' : '商户 KEY 已脱敏';
        $items[] = empty($pay->merchant_pem) ? '商户 PEM 未配置' : '商户 PEM 已脱敏';

        return '<span class="pill '.(empty($pay->merchant_key) || empty($pay->merchant_pem) ? 'closed' : 'open').'">'.e(implode(' · ', $items)).'</span>';
    }

    private function renderSecretValue(?string $value): string
    {
        if (blank($value)) {
            return '<span class="pill closed">未配置</span>';
        }

        return '<span class="pill open">已配置 · 页面已脱敏</span>';
    }

    protected function resourceKey(): string
    {
        return 'pay';
    }
}
