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

    public function test_attach_row_restore_appends_action_when_scope_is_trashed(): void
    {
        $trashScope = \Mockery::mock(AdminTrashScopeService::class);
        $trashScope->shouldReceive('isTrashedScope')->once()->andReturn(true);

        $actions = new class {
            public $appended;

            public function append($action): void
            {
                $this->appended = $action;
            }
        };

        $service = new AdminGridRestoreActionService($trashScope);
        $service->attachRowRestore($actions, \App\Models\Order::class);

        $this->assertInstanceOf(\App\Admin\Actions\Post\Restore::class, $actions->appended);
    }

    public function test_attach_batch_restore_skips_when_scope_is_not_trashed(): void
    {
        $trashScope = \Mockery::mock(AdminTrashScopeService::class);
        $trashScope->shouldReceive('isTrashedScope')->once()->andReturn(false);

        $batch = new class {
            public $added = false;

            public function add($action): void
            {
                $this->added = $action;
            }
        };

        $service = new AdminGridRestoreActionService($trashScope);
        $service->attachBatchRestore($batch, \App\Models\Order::class);

        $this->assertFalse($batch->added);
    }
}
