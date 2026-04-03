<?php

namespace Tests\Unit;

use App\Service\AdminTrashScopeService;
use Tests\TestCase;

class AdminTrashScopeServiceTest extends TestCase
{
    public function test_service_detects_trashed_scope_label(): void
    {
        $service = app(AdminTrashScopeService::class);

        $this->assertTrue($service->isTrashedScope(admin_trans('dujiaoka.trashed')));
        $this->assertFalse($service->isTrashedScope('active'));
    }
}
