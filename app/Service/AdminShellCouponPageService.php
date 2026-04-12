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
                $builder->where('goods.id', $goodsId);
            });
        }

        return $query->paginate(15)->appends($filters);
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

    public function buildHeader(LengthAwarePaginator $coupons): array
    {
        $header = $this->buildResourceHeader('共 '.$coupons->total().' 条优惠码');
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
            $this->buildHeader($coupons),
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

        $names = $coupon->goods->pluck('gd_name')->filter()->values();

        if ($names->isEmpty()) {
            return '未关联商品';
        }

        return $names->implode(' / ');
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
