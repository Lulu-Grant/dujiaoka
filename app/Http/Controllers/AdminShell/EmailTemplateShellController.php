<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellEmailTemplatePageService;
use Illuminate\Http\Request;

class EmailTemplateShellController extends Controller
{
    public function index(Request $request, AdminShellEmailTemplatePageService $pageService)
    {
        $filters = $pageService->extractFilters($request);

        $templates = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($templates, $filters)->toViewData());
    }

    public function show(int $id, AdminShellEmailTemplatePageService $pageService)
    {
        $template = $pageService->find($id);

        return view('admin-shell.pages.show', $pageService->buildShowPageData($template)->toViewData());
    }
}
