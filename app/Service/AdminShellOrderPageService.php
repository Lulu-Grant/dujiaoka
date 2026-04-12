<?php

namespace App\Service;

use App\Models\Order;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellOrderPageService extends AbstractAdminShellPageService
{
    public function extractFilters(Request $request): array
    {
        return [
            'order_sn' => $request->query('order_sn'),
            'title' => $request->query('title'),
            'status' => $request->query('status'),
            'email' => $request->query('email'),
            'trade_no' => $request->query('trade_no'),
            'type' => $request->query('type'),
            'goods_id' => $request->query('goods_id'),
            'coupon_id' => $request->query('coupon_id'),
            'pay_id' => $request->query('pay_id'),
            'start' => $request->query('start'),
            'end' => $request->query('end'),
            'scope' => $request->query('scope'),
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Order::query()
            ->with([
                'goods:id,gd_name',
                'coupon:id,coupon',
                'pay:id,pay_name',
            ])
            ->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['order_sn'])) {
            $query->where('order_sn', $filters['order_sn']);
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%'.$filters['title'].'%');
        }

        if ($this->filled($filters, 'status')) {
            $query->where('status', (int) $filters['status']);
        }

        if (!empty($filters['email'])) {
            $query->where('email', $filters['email']);
        }

        if (!empty($filters['trade_no'])) {
            $query->where('trade_no', $filters['trade_no']);
        }

        if ($this->filled($filters, 'type')) {
            $query->where('type', (int) $filters['type']);
        }

        if ($this->filled($filters, 'goods_id')) {
            $query->where('goods_id', (int) $filters['goods_id']);
        }

        if ($this->filled($filters, 'coupon_id')) {
            $query->where('coupon_id', (int) $filters['coupon_id']);
        }

        if ($this->filled($filters, 'pay_id')) {
            $query->where('pay_id', (int) $filters['pay_id']);
        }

        if (!empty($filters['start'])) {
            $query->where('created_at', '>=', $filters['start']);
        }

        if (!empty($filters['end'])) {
            $query->where('created_at', '<=', $filters['end']);
        }

        return $query->paginate(15)->appends($filters);
    }

    public function find(int $id, ?string $scope = null)
    {
        $query = Order::query()->with([
            'goods:id,gd_name',
            'coupon:id,coupon',
            'pay:id,pay_name',
        ]);

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function buildTable(LengthAwarePaginator $orders, array $filters): array
    {
        $scope = $filters['scope'] ?? null;
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '订单号', '标题', '类型', '邮箱', '商品', '实付金额', '状态', '支付通道', '更新时间', '操作'],
            'rows' => $orders->getCollection()->map(function (Order $order) use ($scope, $definition) {
                return [
                    $order->id,
                    e($order->order_sn),
                    e($order->title),
                    e($this->typeLabel($order->type)),
                    e($order->email),
                    e(optional($order->goods)->gd_name ?: '未关联商品'),
                    e((string) $order->actual_price),
                    $this->renderStatusCell($order),
                    e(optional($order->pay)->pay_name ?: '未选择支付'),
                    e((string) $order->updated_at),
                    $this->renderActionLinks([
                        [
                            'label' => '编辑订单',
                            'href' => admin_url($definition['uri'].'/'.$order->id.'/edit'),
                        ],
                        [
                            'label' => '查看详情',
                            'href' => admin_url($definition['uri'].'/'.$order->id.($scope ? '?scope='.$scope : '')),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有订单记录。',
            'empty_description' => '可以调整订单号、状态、商品、支付通道或范围筛选条件，继续查找订单。',
            'paginator' => $orders,
        ];
    }

    public function buildHeader(LengthAwarePaginator $orders): array
    {
        return $this->buildResourceHeader('共 '.$orders->total().' 条订单');
    }

    public function buildFilters(array $filters): array
    {
        $definition = $this->resourceDefinition();

        return [
            'fields' => [
                ['label' => '订单号', 'name' => 'order_sn', 'value' => $filters['order_sn'] ?? null],
                ['label' => '订单标题', 'name' => 'title', 'value' => $filters['title'] ?? null],
                [
                    'label' => '订单状态',
                    'name' => 'status',
                    'type' => 'select',
                    'value' => $filters['status'] ?? null,
                    'options' => ['' => '全部'] + Order::getStatusMap(),
                ],
                ['label' => '邮箱', 'name' => 'email', 'value' => $filters['email'] ?? null],
                ['label' => '交易号', 'name' => 'trade_no', 'value' => $filters['trade_no'] ?? null],
                [
                    'label' => '订单类型',
                    'name' => 'type',
                    'type' => 'select',
                    'value' => $filters['type'] ?? null,
                    'options' => ['' => '全部'] + Order::getTypeMap(),
                ],
                ['label' => '商品 ID', 'name' => 'goods_id', 'type' => 'number', 'value' => $filters['goods_id'] ?? null],
                ['label' => '优惠码 ID', 'name' => 'coupon_id', 'type' => 'number', 'value' => $filters['coupon_id'] ?? null],
                ['label' => '支付通道 ID', 'name' => 'pay_id', 'type' => 'number', 'value' => $filters['pay_id'] ?? null],
                ['label' => '创建起始', 'name' => 'start', 'type' => 'datetime-local', 'value' => $filters['start'] ?? null],
                ['label' => '创建结束', 'name' => 'end', 'type' => 'datetime-local', 'value' => $filters['end'] ?? null],
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

    public function buildShowHeader(?string $scope = null, ?Order $order = null): array
    {
        $header = $this->buildResourceShowHeader($scope);
        $header['meta'] = $order ? $this->buildReadOnlyMeta($order) : '订单详情与支付、商品和查询信息分开展示，方便人工维护时快速确认上下文。';

        if ($order) {
            $header['actions'][] = [
                'label' => '编辑订单',
                'href' => admin_url('v2/order/'.$order->id.'/edit'),
                'variant' => 'secondary',
            ];
        }

        return $header;
    }

    private function buildReadOnlyMeta(Order $order): string
    {
        return implode(' | ', [
            '订单号：'.$order->order_sn,
            '状态：'.$this->statusLabel($order->status),
            '类型：'.$this->typeLabel($order->type),
            '支付：'.(optional($order->pay)->pay_name ?: '未选择支付'),
            '实付：'.(string) $order->actual_price,
        ]);
    }

    public function buildIndexPageData(LengthAwarePaginator $orders, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($orders),
            $this->buildFilters($filters),
            $this->buildTable($orders, $filters)
        );
    }

    public function buildShowPageData($order, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader($scope, $order),
            $this->detailItems($order)
        );
    }

    public function detailItems(Order $order): array
    {
        return [
            ['label' => 'ID', 'value' => $order->id],
            ['label' => '订单号', 'value' => e($order->order_sn)],
            ['label' => '订单标题', 'value' => e($order->title)],
            ['label' => '订单类型', 'value' => e($this->typeLabel($order->type))],
            ['label' => '订单状态', 'value' => e($this->statusLabel($order->status))],
            ['label' => '邮箱', 'value' => e($order->email)],
            ['label' => '关联商品', 'value' => e(optional($order->goods)->gd_name ?: '未关联商品')],
            ['label' => '商品单价', 'value' => e((string) $order->goods_price)],
            ['label' => '购买数量', 'value' => e((string) $order->buy_amount)],
            ['label' => '总价', 'value' => e((string) $order->total_price)],
            ['label' => '实付金额', 'value' => e((string) $order->actual_price)],
            ['label' => '优惠码', 'value' => e(optional($order->coupon)->coupon ?: '未使用优惠码')],
            ['label' => '优惠抵扣', 'value' => e((string) $order->coupon_discount_price)],
            ['label' => '批发抵扣', 'value' => e((string) $order->wholesale_discount_price)],
            ['label' => '支付通道', 'value' => e(optional($order->pay)->pay_name ?: '未选择支付')],
            ['label' => '交易号', 'value' => e((string) $order->trade_no)],
            ['label' => '查询密码', 'value' => e((string) $order->search_pwd)],
            ['label' => '下单 IP', 'value' => e((string) $order->buy_ip)],
            [
                'label' => '订单附加信息',
                'value' => e((string) $order->info),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap;',
            ],
            ['label' => '创建时间', 'value' => e((string) $order->created_at)],
            ['label' => '更新时间', 'value' => e((string) $order->updated_at)],
            ['label' => '删除状态', 'value' => $order->deleted_at ? '已删除' : '正常'],
        ];
    }

    protected function resourceKey(): string
    {
        return 'order';
    }

    private function renderStatusCell(Order $order): string
    {
        if ($order->deleted_at) {
            return '<span class="pill trashed">回收站</span>';
        }

        $variant = 'closed';

        if ((int) $order->status === Order::STATUS_COMPLETED) {
            $variant = 'open';
        } elseif (in_array((int) $order->status, [Order::STATUS_WAIT_PAY, Order::STATUS_PENDING, Order::STATUS_PROCESSING], true)) {
            $variant = 'warning';
        }

        return sprintf(
            '<span class="pill %s">%s</span>',
            $variant,
            e($this->statusLabel($order->status))
        );
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }

    private function statusLabel($status): string
    {
        $map = Order::getStatusMap();

        return $map[$status] ?? (string) $status;
    }

    private function typeLabel($type): string
    {
        $map = Order::getTypeMap();

        return $map[$type] ?? (string) $type;
    }

    private function filled(array $filters, string $key): bool
    {
        return isset($filters[$key]) && $filters[$key] !== '';
    }
}
