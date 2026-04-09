<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellPayPageService;
use App\Service\PayAdminPresenterService;
use Illuminate\Http\Request;

class PayShellController extends Controller
{
    public function index(
        Request $request,
        AdminShellPayPageService $pageService,
        PayAdminPresenterService $presenter
    ) {
        $filters = [
            'id' => $request->query('id'),
            'pay_check' => $request->query('pay_check'),
            'pay_name' => $request->query('pay_name'),
            'scope' => $request->query('scope'),
        ];

        return view('admin-shell.pay.index', [
            'pays' => $pageService->paginate($filters),
            'filters' => $filters,
            'presenter' => $presenter,
        ]);
    }

    public function show(
        int $id,
        Request $request,
        AdminShellPayPageService $pageService,
        PayAdminPresenterService $presenter
    ) {
        return view('admin-shell.pay.show', [
            'pay' => $pageService->find($id, $request->query('scope')),
            'scope' => $request->query('scope'),
            'presenter' => $presenter,
        ]);
    }
}
