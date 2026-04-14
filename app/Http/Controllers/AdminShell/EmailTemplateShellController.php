<?php

namespace App\Http\Controllers\AdminShell;

use App\Service\AdminShellEmailTemplatePageService;
use Illuminate\Http\Request;

class EmailTemplateShellController extends BaseAdminShellController
{
    protected $resourceKey = 'emailtpl';

    public function index(Request $request)
    {
        if ((string) $request->query('export', '') === 'summary') {
            /** @var \App\Service\AdminShellEmailTemplatePageService $pageService */
            $pageService = app(AdminShellEmailTemplatePageService::class);

            return $pageService->exportSummaryResponse($pageService->extractFilters($request));
        }

        return parent::index($request);
    }
}
