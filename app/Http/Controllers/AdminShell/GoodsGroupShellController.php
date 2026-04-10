<?php

namespace App\Http\Controllers\AdminShell;

use App\Service\AdminShellGoodsGroupPageService;
use Illuminate\Http\Request;

class GoodsGroupShellController extends BaseAdminShellController
{
    public function index(Request $request, AdminShellGoodsGroupPageService $pageService)
    {
        return $this->renderIndexPage($request, $pageService);
    }

    public function show(int $id, Request $request, AdminShellGoodsGroupPageService $pageService)
    {
        return $this->renderShowPage($id, $request, $pageService, true);
    }
}
