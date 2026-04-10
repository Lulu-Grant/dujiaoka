<?php

namespace App\Http\Controllers\AdminShell;

class PayShellController extends BaseAdminShellController
{
    protected $pageServiceClass = \App\Service\AdminShellPayPageService::class;

    protected $usesScope = true;
}
