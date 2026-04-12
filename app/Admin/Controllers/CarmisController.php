<?php

namespace App\Admin\Controllers;

use App\Service\LegacyAdminShellRedirectService;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

class CarmisController extends AdminController
{
    public function index(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceIndex('carmis');
    }

    public function create(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceCreate('carmis');
    }

    public function show($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceShow('carmis', $id);
    }

    public function edit($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceEdit('carmis', $id);
    }

    /**
     * 导入卡密
     *
     * @param Content $content
     * @return Content
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function importCarmis(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toCarmiImport();
    }
}
