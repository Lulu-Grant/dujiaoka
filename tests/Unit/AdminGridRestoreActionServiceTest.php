<?php

namespace Tests\Unit;

use App\Service\AdminGridRestoreActionService;
use App\Service\AdminTrashScopeService;
use Tests\TestCase;

class AdminGridRestoreActionServiceTest extends TestCase
{
    public function test_should_attach_delegates_to_trash_scope_service(): void
    {
        $trashScope = \Mockery::mock(AdminTrashScopeService::class);
        $trashScope->shouldReceive('isTrashedScope')->once()->andReturn(true);

        $service = new AdminGridRestoreActionService($trashScope);

        $this->assertTrue($service->shouldAttach());
    }

    public function test_model_returns_passthrough_model_class(): void
    {
        $trashScope = \Mockery::mock(AdminTrashScopeService::class);
        $service = new AdminGridRestoreActionService($trashScope);

        $this->assertSame(\App\Models\Order::class, $service->model(\App\Models\Order::class));
    }
}
