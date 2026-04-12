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

        if ($request->boolean('export')) {
            $exportFilters = $pageService->normalizeExportFilters($filters);
            $content = $pageService->exportText($exportFilters);
            $filename = 'carmis-export-'.now()->format('YmdHis').'.txt';

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        $records = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($records, $filters)->toViewData());
    }
}
