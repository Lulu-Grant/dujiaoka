<?php

namespace App\Admin\Controllers;

use App\Service\LegacyAdminShellRedirectService;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Http\Controllers\AdminController;

class CouponController extends AdminController
{
    public function index(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceIndex('coupon');
    }

    public function create(Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceCreate('coupon');
    }

    public function show($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceShow('coupon', $id);
    }

    public function edit($id, Content $content)
    {
        return app(LegacyAdminShellRedirectService::class)->toResourceEdit('coupon', $id);
    }
}
