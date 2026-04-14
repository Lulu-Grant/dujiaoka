<?php

namespace App\Http\Controllers\AdminShell;

use Illuminate\Http\Request;

class PayShellController extends BaseAdminShellController
{
    protected $resourceKey = 'pay';

    public function index(Request $request)
    {
        if ($request->boolean('export')) {
            $pageService = $this->resolvePageService();
            $filters = $pageService->extractFilters($request);
            $content = $pageService->exportText($filters);
            $filename = 'pay-export-'.now()->format('Ymd-His').'.txt';

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        return parent::index($request);
    }
}
