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

        return view('admin-shell.emailtpl.index', [
            'templates' => $pageService->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function show(int $id, AdminShellEmailTemplatePageService $pageService)
    {
        return view('admin-shell.emailtpl.show', [
            'template' => $pageService->find($id),
        ]);
    }
}
