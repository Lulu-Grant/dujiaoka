<?php

namespace App\Http\Controllers\AdminShell;

use App\Service\AdminShellPayPageService;
use Illuminate\Http\Request;

class PayShellController extends BaseAdminShellController
{
    public function index(Request $request, AdminShellPayPageService $pageService)
    {
        return $this->renderIndexPage($request, $pageService);
    }

    public function show(int $id, Request $request, AdminShellPayPageService $pageService)
    {
        return $this->renderShowPage($id, $request, $pageService, true);
    }
}
