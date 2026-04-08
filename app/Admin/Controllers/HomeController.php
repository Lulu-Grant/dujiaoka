<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Service\AdminDashboardLayoutService;
use Dcat\Admin\Layout\Content;

class HomeController extends Controller
{

    public function index(Content $content)
    {
        return app(AdminDashboardLayoutService::class)->build($content);
    }

    public static function title()
    {
        return app(AdminDashboardLayoutService::class)->titleView();
    }
}
