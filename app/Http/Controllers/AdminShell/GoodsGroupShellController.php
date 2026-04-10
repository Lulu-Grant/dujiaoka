<?php

namespace App\Http\Controllers\AdminShell;

class GoodsGroupShellController extends BaseAdminShellController
{
    protected $pageServiceClass = \App\Service\AdminShellGoodsGroupPageService::class;

    protected $usesScope = true;
}
