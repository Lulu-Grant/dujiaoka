<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellGoodsGroupPageService;
use Illuminate\Http\Request;

class GoodsGroupShellController extends Controller
{
    public function index(Request $request, AdminShellGoodsGroupPageService $pageService)
    {
        $filters = $pageService->extractFilters($request);

        $groups = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($groups, $filters));
    }

    public function show(int $id, Request $request, AdminShellGoodsGroupPageService $pageService)
    {
        $filters = $pageService->extractFilters($request);
        $group = $pageService->find($id, $filters['scope'] ?? null);

        return view('admin-shell.pages.show', $pageService->buildShowPageData($group, $filters['scope'] ?? null));
    }
}
