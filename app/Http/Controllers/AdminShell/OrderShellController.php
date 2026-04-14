<?php

namespace App\Http\Controllers\AdminShell;

use App\Service\AdminShellOrderPageService;
use Illuminate\Http\Request;

class OrderShellController extends BaseAdminShellController
{
    protected $resourceKey = 'order';

    public function index(Request $request)
    {
        $export = (string) $request->query('export', '');

        if ($export !== '') {
            /** @var \App\Service\AdminShellOrderPageService $pageService */
            $pageService = app(AdminShellOrderPageService::class);

            return $pageService->export($pageService->extractFilters($request), $export);
        }

        return parent::index($request);
    }
}
