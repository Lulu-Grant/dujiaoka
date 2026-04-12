<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Service\LegacyAdminShellRedirectService;

class HomeController extends Controller
{
    public function index()
    {
        return app(LegacyAdminShellRedirectService::class)->toDashboard();
    }
}
