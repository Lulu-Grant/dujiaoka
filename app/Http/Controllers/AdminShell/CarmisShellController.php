<?php

namespace App\Http\Controllers\AdminShell;

use Illuminate\Http\Request;

class CarmisShellController extends BaseAdminShellController
{
    protected $resourceKey = 'carmis';

    public function index(Request $request)
    {
        $pageService = $this->resolvePageService();
        $filters = $pageService->extractFilters($request);

        $export = strtolower(trim((string) $request->query('export', '')));

        if ($export !== '') {
            $exportFilters = $pageService->normalizeExportFilters($filters);

            if ($export === 'csv') {
                return $pageService->exportCsvResponse($exportFilters);
            }

            return $pageService->exportTextResponse($exportFilters);
        }

        $records = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($records, $filters)->toViewData());
    }
}
