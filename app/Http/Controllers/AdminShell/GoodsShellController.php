<?php

namespace App\Http\Controllers\AdminShell;

use Illuminate\Http\Request;

class GoodsShellController extends BaseAdminShellController
{
    protected $resourceKey = 'goods';

    public function index(Request $request)
    {
        $pageService = $this->resolvePageService();
        $filters = $pageService->extractFilters($request);
        $export = (string) $request->query('export', '');

        if ($export !== '') {
            return $pageService->export($filters, $export);
        }

        $records = $pageService->paginate($filters);
        $page = $pageService->buildIndexPageData($records, $filters)->toViewData();

        $page['maintenanceNote'] = '商品壳页优先用于查找、核对和进入编辑页，复杂库存或批量动作仍建议先走详情确认。';

        return view('admin-shell.goods.index', $page);
    }

    public function show(int $id, Request $request)
    {
        $pageService = $this->resolvePageService();
        $filters = $pageService->extractFilters($request);
        $scope = $filters['scope'] ?? null;
        $resource = $this->resolveResource();

        $record = $resource['uses_scope']
            ? $pageService->find($id, $scope)
            : $pageService->find($id);

        return view('admin-shell.goods.show', $pageService->buildShowViewData($record, $scope));
    }
}
