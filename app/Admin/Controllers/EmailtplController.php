<?php

namespace App\Admin\Controllers;

use App\Service\LegacyAdminShellRedirectService;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

class EmailtplController extends AdminController
{
    public function index(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceIndex('emailtpl');
    }

    public function create(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceCreate('emailtpl');
    }

    public function show($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceShow('emailtpl', $id);
    }

    public function edit($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceEdit('emailtpl', $id);
    }
}
