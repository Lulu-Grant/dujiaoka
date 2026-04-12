<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Service\AdminShellDashboardPageService;

class DashboardShellController extends Controller
{
    /**
     * @var \App\Service\AdminShellDashboardPageService
     */
    private $dashboardPageService;

    public function __construct(AdminShellDashboardPageService $dashboardPageService)
    {
        $this->dashboardPageService = $dashboardPageService;
    }

    public function index()
    {
        return view('admin-shell.dashboard.index', $this->dashboardPageService->buildPageData());
    }
}
