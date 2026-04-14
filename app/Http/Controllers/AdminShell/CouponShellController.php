<?php

namespace App\Http\Controllers\AdminShell;

use Illuminate\Http\Request;

class CouponShellController extends BaseAdminShellController
{
    protected $resourceKey = 'coupon';

    public function index(Request $request)
    {
        $pageService = $this->resolvePageService();
        $filters = $pageService->extractFilters($request);

        if ($request->query('export') === 'text') {
            return $pageService->exportTextResponse($filters);
        }

        $records = $pageService->paginate($filters);
        $page = array_merge(
            $pageService->buildIndexPageData($records, $filters)->toViewData(),
            $pageService->buildIndexViewData($records, $filters)
        );

        return view('admin-shell.coupon.index', $page);
    }

    public function show(int $id, Request $request)
    {
        $pageService = $this->resolvePageService();
        $filters = $pageService->extractFilters($request);
        $scope = $filters['scope'] ?? null;
        $record = $pageService->find($id, $scope);
        $page = array_merge(
            $pageService->buildShowPageData($record, $scope)->toViewData(),
            $pageService->buildShowViewData($record, $scope)
        );

        return view('admin-shell.coupon.show', $page);
    }
}
