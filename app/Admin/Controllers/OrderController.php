<?php

namespace App\Admin\Controllers;

use App\Service\LegacyAdminShellRedirectService;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

class OrderController extends AdminController
{
    public function index(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceIndex('order');
    }

    public function show($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceShow('order', $id);
    }

    public function edit($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceEdit('order', $id);
    }
}
