<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellPayPageService;
use Illuminate\Http\Request;

class PayShellController extends Controller
{
    public function index(Request $request, AdminShellPayPageService $pageService)
    {
        $filters = $pageService->extractFilters($request);

        $pays = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($pays, $filters)->toViewData());
    }

    public function show(int $id, Request $request, AdminShellPayPageService $pageService)
    {
        $filters = $pageService->extractFilters($request);
        $pay = $pageService->find($id, $filters['scope'] ?? null);

        return view('admin-shell.pages.show', $pageService->buildShowPageData($pay, $filters['scope'] ?? null)->toViewData());
    }
}
