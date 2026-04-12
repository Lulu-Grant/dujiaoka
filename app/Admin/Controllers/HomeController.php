<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Service\LegacyAdminShellRedirectService;
use App\Service\AdminDashboardLayoutService;

class HomeController extends Controller
{
    public function index()
    {
        return app(LegacyAdminShellRedirectService::class)->toDashboard();
    }

    public static function title()
    {
        return app(AdminDashboardLayoutService::class)->titleView();
    }
}
