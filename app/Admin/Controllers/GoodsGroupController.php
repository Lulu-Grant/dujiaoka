<?php

namespace App\Admin\Controllers;

use App\Service\LegacyAdminShellRedirectService;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

class GoodsGroupController extends AdminController
{
    public function index(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceIndex('goods-group');
    }

    public function create(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceCreate('goods-group');
    }

    public function show($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceShow('goods-group', $id);
    }

    public function edit($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceEdit('goods-group', $id);
    }
}
