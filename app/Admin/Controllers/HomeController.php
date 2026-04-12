<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Service\AdminDashboardLayoutService;

class HomeController extends Controller
{
    public function index()
    {
        return redirect(admin_url('v2/dashboard'));
    }

    public static function title()
    {
        return app(AdminDashboardLayoutService::class)->titleView();
    }
}
