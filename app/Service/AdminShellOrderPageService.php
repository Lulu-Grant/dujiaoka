<?php

namespace App\Service;

use App\Models\Order;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
        return $this->buildQuery($filters)->paginate(15)->appends($filters);
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
        return $this->buildHeaderWithFilters($orders, []);
    }

    public function buildHeaderWithFilters(LengthAwarePaginator $orders, array $filters): array
    {
        $header = $this->buildResourceHeader('共 '.$orders->total().' 条订单');
        $header['actions'][] = [
            'label' => '批量更新订单状态',
            'href' => admin_url('v2/order/batch-status'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '批量设置订单类型',
            'href' => admin_url('v2/order/batch-type'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '批量重置查询密码',
            'href' => admin_url('v2/order/batch-reset-search-pwd'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '导出文本',
            'href' => $this->exportUrl($filters, 'text'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '导出 CSV',
            'href' => $this->exportUrl($filters, 'csv'),
            'variant' => 'secondary',
        ];

        return $header;
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
            '基础：'.$order->order_sn.' / '.$this->statusLabel($order->status).' / '.$this->typeLabel($order->type),
            '交易：'.(optional($order->goods)->gd_name ?: '未关联商品').' / '.(optional($order->pay)->pay_name ?: '未选择支付'),
            '金额：'.(string) $order->actual_price.' / 交易号 '.($order->trade_no ?: '未生成'),
        ]);
    }

    public function buildIndexPageData(LengthAwarePaginator $orders, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeaderWithFilters($orders, $filters),
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
            [
                'label' => '基础信息',
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; line-height: 1.75;',
                'value' => e(implode("\n", [
                    '订单号：'.$order->order_sn,
                    '订单标题：'.$order->title,
                    '邮箱：'.$order->email,
                    '订单状态：'.$this->statusLabel($order->status),
                    '订单类型：'.$this->typeLabel($order->type),
                ])),
            ],
            [
                'label' => '商品与支付',
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; line-height: 1.75;',
                'value' => e(implode("\n", [
                    '关联商品：'.(optional($order->goods)->gd_name ?: '未关联商品'),
                    '支付通道：'.(optional($order->pay)->pay_name ?: '未选择支付'),
                    '商品单价：'.(string) $order->goods_price,
                    '购买数量：'.(string) $order->buy_amount,
                ])),
            ],
            [
                'label' => '金额与履约',
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; line-height: 1.75;',
                'value' => e(implode("\n", [
                    '总价：'.(string) $order->total_price,
                    '实付金额：'.(string) $order->actual_price,
                    '优惠码：'.(optional($order->coupon)->coupon ?: '未使用优惠码'),
                    '优惠抵扣：'.(string) $order->coupon_discount_price,
                    '批发抵扣：'.(string) $order->wholesale_discount_price,
                ])),
            ],
            [
                'label' => '维护信息',
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; line-height: 1.75;',
                'value' => e(implode("\n", [
                    '交易号：'.(string) $order->trade_no,
                    '查询密码：'.(string) $order->search_pwd,
                    '下单 IP：'.(string) $order->buy_ip,
                    '创建时间：'.(string) $order->created_at,
                    '更新时间：'.(string) $order->updated_at,
                    '删除状态：'.($order->deleted_at ? '已删除' : '正常'),
                ])),
            ],
            [
                'label' => '订单附加信息',
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap;',
                'value' => e((string) $order->info ?: '暂无附加信息'),
            ],
        ];
    }

    public function export(array $filters, string $format = 'text')
    {
        $format = strtolower(trim($format));
        $orders = $this->buildQuery($filters)->get();
        $content = $format === 'csv'
            ? $this->buildCsvExport($orders)
            : $this->buildTextExport($orders, $filters);
        $filename = 'orders-'.date('Ymd-His').($format === 'csv' ? '.csv' : '.txt');
        $contentType = $format === 'csv' ? 'text/csv; charset=UTF-8' : 'text/plain; charset=UTF-8';

        return response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
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

    private function buildQuery(array $filters): Builder
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

        return $query;
    }

    private function buildTextExport(Collection $orders, array $filters): string
    {
        $lines = [
            '订单导出',
            '筛选条件：'.$this->describeFilters($filters),
            '导出数量：'.$orders->count(),
            str_repeat('=', 48),
        ];

        foreach ($orders as $index => $order) {
            $lines[] = sprintf('[%d] %s', $index + 1, $order->order_sn);
            $lines[] = '标题：'.$order->title;
            $lines[] = '状态：'.$this->statusLabel($order->status);
            $lines[] = '类型：'.$this->typeLabel($order->type);
            $lines[] = '邮箱：'.$order->email;
            $lines[] = '商品：'.(optional($order->goods)->gd_name ?: '未关联商品');
            $lines[] = '支付通道：'.(optional($order->pay)->pay_name ?: '未选择支付');
            $lines[] = '总价：'.(string) $order->total_price.' / 实付：'.(string) $order->actual_price;
            $lines[] = '优惠码：'.(optional($order->coupon)->coupon ?: '未使用优惠码');
            $lines[] = '查询密码：'.($order->search_pwd ?: '未设置');
            $lines[] = '交易号：'.($order->trade_no ?: '未生成');
            $lines[] = '更新时间：'.(string) $order->updated_at;
            $lines[] = str_repeat('-', 48);
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function buildCsvExport(Collection $orders): string
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [
            'ID',
            '订单号',
            '标题',
            '状态',
            '类型',
            '邮箱',
            '商品',
            '支付通道',
            '总价',
            '实付金额',
            '优惠码',
            '查询密码',
            '交易号',
            '更新时间',
        ]);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->id,
                $order->order_sn,
                $order->title,
                $this->statusLabel($order->status),
                $this->typeLabel($order->type),
                $order->email,
                optional($order->goods)->gd_name ?: '未关联商品',
                optional($order->pay)->pay_name ?: '未选择支付',
                $order->total_price,
                $order->actual_price,
                optional($order->coupon)->coupon ?: '未使用优惠码',
                $order->search_pwd ?: '未设置',
                $order->trade_no ?: '未生成',
                (string) $order->updated_at,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content ?: '';
    }

    private function describeFilters(array $filters): string
    {
        $parts = [];

        foreach ([
            '订单号' => 'order_sn',
            '标题' => 'title',
            '状态' => 'status',
            '邮箱' => 'email',
            '交易号' => 'trade_no',
            '类型' => 'type',
            '商品ID' => 'goods_id',
            '优惠码ID' => 'coupon_id',
            '支付通道ID' => 'pay_id',
            '起始' => 'start',
            '结束' => 'end',
            '范围' => 'scope',
        ] as $label => $key) {
            if (!empty($filters[$key]) || (isset($filters[$key]) && $filters[$key] === '0')) {
                $parts[] = $label.'='.($filters[$key] ?? '');
            }
        }

        return $parts ? implode('；', $parts) : '无';
    }

    public function exportUrl(array $filters, string $format): string
    {
        $query = array_merge($this->exportQuery($filters), ['export' => $format]);

        return admin_url('v2/order?'.http_build_query($query));
    }

    private function exportQuery(array $filters): array
    {
        return array_filter($filters, static function ($value) {
            return $value !== null && $value !== '';
        });
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
