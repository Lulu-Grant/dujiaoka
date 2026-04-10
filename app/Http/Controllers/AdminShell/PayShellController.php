<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellPayPageService;
use Illuminate\Http\Request;

class PayShellController extends Controller
{
    public function index(Request $request, AdminShellPayPageService $pageService)
    {
        $filters = [
            'id' => $request->query('id'),
            'pay_check' => $request->query('pay_check'),
            'pay_name' => $request->query('pay_name'),
            'scope' => $request->query('scope'),
        ];

        $pays = $pageService->paginate($filters);

        return view('admin-shell.pages.index', $pageService->buildIndexPageData($pays, $filters));
    }

    public function show(int $id, Request $request, AdminShellPayPageService $pageService)
    {
        $pay = $pageService->find($id, $request->query('scope'));

        return view('admin-shell.pages.show', $pageService->buildShowPageData($pay, $request->query('scope')));
    }
}
