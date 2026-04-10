<?php

namespace App\Http\Controllers\AdminShell;

class EmailTemplateShellController extends BaseAdminShellController
{
    protected $pageServiceClass = \App\Service\AdminShellEmailTemplatePageService::class;
}
