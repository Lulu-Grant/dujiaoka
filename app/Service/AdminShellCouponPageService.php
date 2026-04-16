<?php

namespace App\Service;

use App\Models\Coupon;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellCouponPageService extends AbstractAdminShellPageService
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

    public function extractFilters(Request $request): array
    {
        return [
            'id' => $request->query('id'),
            'coupon' => $request->query('coupon'),
            'goods_id' => $request->query('goods_id'),
            'scope' => $request->query('scope'),
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)->paginate(15)->appends($filters);
    }

    public function find(int $id, ?string $scope = null)
    {
        $query = Coupon::query()->with('goods:id,gd_name');

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function buildTable(LengthAwarePaginator $coupons, array $filters): array
    {
        $scope = $filters['scope'] ?? null;
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '优惠码', '折扣金额', '使用状态', '启用状态', '可用次数', '关联商品', '更新时间', '操作'],
            'rows' => $coupons->getCollection()->map(function (Coupon $coupon) use ($scope, $definition) {
                return [
                    $coupon->id,
                    e($coupon->coupon),
                    e((string) $coupon->discount),
                    $this->renderUsageCell($coupon),
                    $this->renderOpenCell($coupon),
                    $coupon->ret,
                    e($this->goodsSummary($coupon)),
                    e((string) $coupon->updated_at),
                    $this->renderActionLinks([
                        [
                            'label' => '编辑优惠码',
                            'href' => admin_url($definition['uri'].'/'.$coupon->id.'/edit'),
                        ],
                        [
                            'label' => '查看详情',
                            'href' => admin_url($definition['uri'].'/'.$coupon->id.($scope ? '?scope='.$scope : '')),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有优惠码记录。',
            'empty_description' => '可以调整优惠码、商品或范围筛选条件，继续查找优惠码。',
            'paginator' => $coupons,
        ];
    }

    public function buildHeader(LengthAwarePaginator $coupons, array $filters): array
    {
        $header = $this->buildResourceHeader('共 '.$coupons->total().' 条优惠码');
        $header['actions'][] = [
            'label' => '导出优惠码文本',
            'href' => admin_url($this->resourceDefinition()['uri']).'?'.$this->buildExportQuery($filters, 'text'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '导出优惠码 CSV',
            'href' => admin_url($this->resourceDefinition()['uri']).'?'.$this->buildExportQuery($filters, 'csv'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '批量启停优惠码',
            'href' => admin_url('v2/coupon/batch-status'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '批量设置使用状态',
            'href' => admin_url('v2/coupon/batch-use'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '批量设置折扣',
            'href' => admin_url('v2/coupon/batch-discount'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '批量设置可用次数',
            'href' => admin_url('v2/coupon/batch-ret'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '批量生成优惠码',
            'href' => admin_url('v2/coupon/create?mode=batch'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '新建优惠码',
            'href' => admin_url('v2/coupon/create'),
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
                ['label' => '优惠码', 'name' => 'coupon', 'value' => $filters['coupon'] ?? null],
                ['label' => '商品 ID', 'name' => 'goods_id', 'type' => 'number', 'value' => $filters['goods_id'] ?? null],
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

    public function buildIndexPageData(LengthAwarePaginator $coupons, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($coupons, $filters),
            $this->buildFilters($filters),
            $this->buildTable($coupons, $filters)
        );
    }

    public function buildShowPageData($coupon, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader($scope),
            $this->detailItems($coupon)
        );
    }

    public function buildIndexViewData(LengthAwarePaginator $coupons, array $filters): array
    {
        return [
            'maintenanceNote' => '优惠码壳页优先用于查找、复制、核对和进入编辑页。批量改动前建议先在详情页确认关联商品和启用状态。',
            'summaryCards' => $this->buildIndexSummaryCards($coupons),
            'quickTips' => [
                '可以先按优惠码或商品 ID 定位，再进入详情页核对关联商品。',
                '回收站模式适合检查已删除优惠码的历史状态。',
            ],
        ];
    }

    public function exportTextResponse(array $filters)
    {
        $content = $this->exportText($filters);
        $filename = $this->buildExportFilename('txt');

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportCsvResponse(array $filters)
    {
        $content = $this->exportCsv($filters);
        $filename = $this->buildExportFilename('csv');

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function buildShowViewData(Coupon $coupon, ?string $scope = null): array
    {
        return [
            'maintenanceNote' => '详情页已支持复制优惠码并快速进入编辑页，适合日常核对和低风险维护。',
            'summaryCards' => $this->buildShowSummaryCards($coupon),
            'couponCode' => $coupon->coupon,
            'couponCopyLabel' => '复制优惠码',
            'couponCopyHint' => '复制后可直接用于前台测试或订单核对。',
            'couponEditUrl' => admin_url($this->resourceDefinition()['uri'].'/'.$coupon->id.'/edit'),
            'couponScope' => $scope,
        ];
    }

    public function detailItems(Coupon $coupon): array
    {
        return [
            ['label' => 'ID', 'value' => $coupon->id],
            ['label' => '优惠码', 'value' => e($coupon->coupon)],
            ['label' => '折扣金额', 'value' => e((string) $coupon->discount)],
            ['label' => '使用状态', 'value' => e(strip_tags($this->statusPresenter->couponUsageLabel($coupon->is_use)))],
            ['label' => '启用状态', 'value' => e(strip_tags($this->statusPresenter->openStatusLabel($coupon->is_open)))],
            ['label' => '可用次数', 'value' => $coupon->ret],
            ['label' => '关联商品', 'value' => e($this->goodsSummary($coupon))],
            ['label' => '创建时间', 'value' => e((string) $coupon->created_at)],
            ['label' => '更新时间', 'value' => e((string) $coupon->updated_at)],
            ['label' => '删除状态', 'value' => $coupon->deleted_at ? '已删除' : '正常'],
        ];
    }

    protected function resourceKey(): string
    {
        return 'coupon';
    }

    private function renderUsageCell(Coupon $coupon): string
    {
        return sprintf(
            '<span class="pill %s">%s</span>',
            (int) $coupon->is_use === Coupon::STATUS_USE ? 'closed' : 'open',
            e(strip_tags($this->statusPresenter->couponUsageLabel($coupon->is_use)))
        );
    }

    private function renderOpenCell(Coupon $coupon): string
    {
        if ($coupon->deleted_at) {
            return '<span class="pill trashed">回收站</span>';
        }

        return sprintf(
            '<span class="pill %s">%s</span>',
            (int) $coupon->is_open ? 'open' : 'closed',
            e(strip_tags($this->statusPresenter->openStatusLabel($coupon->is_open)))
        );
    }

    private function goodsSummary(Coupon $coupon): string
    {
        if (!$coupon->relationLoaded('goods')) {
            return '未加载';
        }

        $names = $coupon->goods->pluck('gd_name')->filter()->unique()->values();

        if ($names->isEmpty()) {
            return '未关联商品';
        }

        return $names->implode(' / ');
    }

    private function buildIndexSummaryCards(LengthAwarePaginator $coupons): array
    {
        $collection = $coupons->getCollection();

        $activeCount = $collection->filter(function (Coupon $coupon) {
            return (int) $coupon->is_open === Coupon::STATUS_OPEN;
        })->count();
        $usedCount = $collection->filter(function (Coupon $coupon) {
            return (int) $coupon->is_use === Coupon::STATUS_USE;
        })->count();
        $linkedCount = $collection->filter(function (Coupon $coupon) {
            return $coupon->relationLoaded('goods') && $coupon->goods->isNotEmpty();
        })->count();

        return [
            [
                'label' => '当前结果',
                'value' => '<strong>'.$coupons->total().'</strong><span style="margin-left: 10px; color: #66756b;">条记录</span>',
            ],
            [
                'label' => '当前页',
                'value' => '<strong>'.$collection->count().'</strong><span style="margin-left: 10px; color: #66756b;">条记录</span>',
            ],
            [
                'label' => '已启用',
                'value' => '<strong>'.$activeCount.'</strong><span style="margin-left: 10px; color: #66756b;">条记录</span>',
            ],
            [
                'label' => '已使用',
                'value' => '<strong>'.$usedCount.'</strong><span style="margin-left: 10px; color: #66756b;">条记录</span>',
            ],
            [
                'label' => '已关联商品',
                'value' => '<strong>'.$linkedCount.'</strong><span style="margin-left: 10px; color: #66756b;">条记录</span>',
            ],
        ];
    }

    private function buildShowSummaryCards(Coupon $coupon): array
    {
        $linkedCount = $coupon->relationLoaded('goods') ? $coupon->goods->count() : 0;

        return [
            [
                'label' => '优惠码',
                'value' => '<strong>'.e($coupon->coupon).'</strong><span style="margin-left: 10px; color: #66756b;">可复制到前台或测试单</span>',
            ],
            [
                'label' => '折扣金额',
                'value' => '<strong>'.e((string) $coupon->discount).'</strong><span style="margin-left: 10px; color: #66756b;">元</span>',
            ],
            [
                'label' => '使用状态',
                'value' => e(strip_tags($this->statusPresenter->couponUsageLabel($coupon->is_use))),
            ],
            [
                'label' => '启用状态',
                'value' => e(strip_tags($this->statusPresenter->openStatusLabel($coupon->is_open))),
            ],
            [
                'label' => '可用次数',
                'value' => '<strong>'.e((string) $coupon->ret).'</strong><span style="margin-left: 10px; color: #66756b;">次</span>',
            ],
            [
                'label' => '关联商品',
                'value' => '<strong>'.$linkedCount.'</strong><span style="margin-left: 10px; color: #66756b;">个</span>',
            ],
        ];
    }

    private function filteredQuery(array $filters)
    {
        $query = Coupon::query()->with('goods:id,gd_name')->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['coupon'])) {
            $query->where('coupon', 'like', '%'.$filters['coupon'].'%');
        }

        if (!empty($filters['goods_id'])) {
            $goodsId = (int) $filters['goods_id'];
            $query->whereHas('goods', function ($builder) use ($goodsId) {
                $builder->whereKey($goodsId);
            });
        }

        return $query;
    }

    private function exportText(array $filters): string
    {
        $coupons = $this->filteredQuery($filters)->get();
        $lines = [
            '独角数卡西瓜版 - 优惠码文本导出',
            '导出时间：'.now()->format('Y-m-d H:i:s'),
            '筛选条件：'.$this->describeFilters($filters),
            '导出数量：'.$coupons->count(),
            '',
        ];

        foreach ($coupons as $index => $coupon) {
            $lines[] = sprintf('%d. 优惠码：%s', $index + 1, $coupon->coupon);
            $lines[] = '   ID：'.$coupon->id;
            $lines[] = '   折扣：'.$coupon->discount;
            $lines[] = '   启用状态：'.((int) $coupon->is_open === Coupon::STATUS_OPEN ? '已启用' : '已停用');
            $lines[] = '   使用状态：'.((int) $coupon->is_use === Coupon::STATUS_USE ? '已使用' : '未使用');
            $lines[] = '   可用次数：'.$coupon->ret;
            $lines[] = '   关联商品：'.$this->goodsSummary($coupon);
            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }

    private function exportCsv(array $filters): string
    {
        $coupons = $this->filteredQuery($filters)->get();
        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, ['优惠码', 'ID', '折扣金额', '使用状态', '启用状态', '可用次数', '关联商品', '删除状态', '更新时间']);

        foreach ($coupons as $coupon) {
            fputcsv($stream, [
                $coupon->coupon,
                $coupon->id,
                $coupon->discount,
                (int) $coupon->is_use === Coupon::STATUS_USE ? '已使用' : '未使用',
                $coupon->deleted_at ? '回收站' : ((int) $coupon->is_open === Coupon::STATUS_OPEN ? '已启用' : '已停用'),
                $coupon->ret,
                $this->goodsSummary($coupon),
                $coupon->deleted_at ? '已删除' : '正常',
                (string) $coupon->updated_at,
            ]);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }

    private function buildExportFilename(string $extension = 'txt'): string
    {
        return 'coupon-export-'.now()->format('Ymd-His').'.'.$extension;
    }

    private function buildExportQuery(array $filters, string $exportType = 'text'): string
    {
        $query = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        unset($query['export']);
        $query['export'] = $exportType;

        return http_build_query($query);
    }

    private function describeFilters(array $filters): string
    {
        $segments = [];

        if (!empty($filters['id'])) {
            $segments[] = 'ID='.$filters['id'];
        }

        if (!empty($filters['coupon'])) {
            $segments[] = '优惠码包含“'.$filters['coupon'].'”';
        }

        if (!empty($filters['goods_id'])) {
            $segments[] = '商品ID='.$filters['goods_id'];
        }

        if (($filters['scope'] ?? null) === 'trashed') {
            $segments[] = '范围=回收站';
        }

        return empty($segments) ? '全部优惠码' : implode('，', $segments);
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
