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

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($templates, $filters));
    }

    public function show(int $id, AdminShellEmailTemplatePageService $pageService)
    {
        $template = $pageService->find($id);

        return view('admin-shell.pages.show', $pageService->buildShowPageData($template));
    }
}
