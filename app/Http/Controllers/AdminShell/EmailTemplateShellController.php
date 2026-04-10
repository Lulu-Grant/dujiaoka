<?php

namespace App\Http\Controllers\AdminShell;

use App\Service\AdminShellEmailTemplatePageService;
use Illuminate\Http\Request;

class EmailTemplateShellController extends BaseAdminShellController
{
    public function index(Request $request, AdminShellEmailTemplatePageService $pageService)
    {
        return $this->renderIndexPage($request, $pageService);
    }

    public function show(int $id, Request $request, AdminShellEmailTemplatePageService $pageService)
    {
        return $this->renderShowPage($id, $request, $pageService);
    }
}
