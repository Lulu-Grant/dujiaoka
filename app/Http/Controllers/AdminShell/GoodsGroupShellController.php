<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellGoodsGroupPageService;
use App\Service\AdminStatusPresenterService;
use Illuminate\Http\Request;

class GoodsGroupShellController extends Controller
{
    public function index(
        Request $request,
        AdminShellGoodsGroupPageService $pageService,
        AdminStatusPresenterService $statusPresenter
    ) {
        $filters = [
            'id' => $request->query('id'),
            'scope' => $request->query('scope'),
        ];

        return view('admin-shell.goods-group.index', [
            'groups' => $pageService->paginate($filters),
            'filters' => $filters,
            'statusPresenter' => $statusPresenter,
        ]);
    }

    public function show(
        int $id,
        Request $request,
        AdminShellGoodsGroupPageService $pageService,
        AdminStatusPresenterService $statusPresenter
    ) {
        return view('admin-shell.goods-group.show', [
            'group' => $pageService->find($id, $request->query('scope')),
            'scope' => $request->query('scope'),
            'statusPresenter' => $statusPresenter,
        ]);
    }
}
