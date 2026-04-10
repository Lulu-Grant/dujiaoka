<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellEmailTemplatePageService;
use Illuminate\Http\Request;

class EmailTemplateShellController extends Controller
{
    public function index(Request $request, AdminShellEmailTemplatePageService $pageService)
    {
        $filters = [
            'id' => $request->query('id'),
            'tpl_name' => $request->query('tpl_name'),
            'tpl_token' => $request->query('tpl_token'),
        ];

        $templates = $pageService->paginate($filters);

        return view('admin-shell.emailtpl.index', [
            'templates' => $templates,
            'filters' => $filters,
            'header' => $pageService->buildHeader($templates),
            'filterPanel' => $pageService->buildFilters($filters),
            'table' => $pageService->buildTable($templates),
        ]);
    }

    public function show(int $id, AdminShellEmailTemplatePageService $pageService)
    {
        $template = $pageService->find($id);

        return view('admin-shell.emailtpl.show', [
            'template' => $template,
            'items' => $pageService->detailItems($template),
        ]);
    }
}
