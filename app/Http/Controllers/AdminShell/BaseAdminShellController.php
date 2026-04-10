<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseAdminShellController extends Controller
{
    protected function renderIndexPage(Request $request, $pageService)
    {
        $filters = $pageService->extractFilters($request);
        $records = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($records, $filters)->toViewData());
    }

    protected function renderShowPage(int $id, Request $request, $pageService, bool $usesScope = false)
    {
        $filters = $pageService->extractFilters($request);
        $scope = $filters['scope'] ?? null;

        $record = $usesScope
            ? $pageService->find($id, $scope)
            : $pageService->find($id);

        $page = $usesScope
            ? $pageService->buildShowPageData($record, $scope)
            : $pageService->buildShowPageData($record);

        return view('admin-shell.pages.show', $page->toViewData());
    }
}
