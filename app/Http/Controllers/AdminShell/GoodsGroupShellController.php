<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellGoodsGroupPageService;
use Illuminate\Http\Request;

class GoodsGroupShellController extends Controller
{
    public function index(Request $request, AdminShellGoodsGroupPageService $pageService)
    {
        $filters = [
            'id' => $request->query('id'),
            'scope' => $request->query('scope'),
        ];

        $groups = $pageService->paginate($filters);

        return view('admin-shell.goods-group.index', [
            'groups' => $groups,
            'filters' => $filters,
            'header' => $pageService->buildHeader($groups),
            'filterPanel' => $pageService->buildFilters($filters),
            'table' => $pageService->buildTable($groups, $filters),
        ]);
    }

    public function show(int $id, Request $request, AdminShellGoodsGroupPageService $pageService)
    {
        $group = $pageService->find($id, $request->query('scope'));

        return view('admin-shell.goods-group.show', [
            'group' => $group,
            'header' => $pageService->buildShowHeader($request->query('scope')),
            'items' => $pageService->detailItems($group),
            'scope' => $request->query('scope'),
        ]);
    }
}
