<?php

namespace App\Service;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;

class AdminGridRestoreActionService
{
    /**
     * @var AdminTrashScopeService
     */
    private $trashScopeService;

    public function __construct(AdminTrashScopeService $trashScopeService)
    {
        $this->trashScopeService = $trashScopeService;
    }

    public function shouldAttach(): bool
    {
        return $this->trashScopeService->isTrashedScope();
    }

    public function model(string $modelClass): string
    {
        return $modelClass;
    }

    public function attachRowRestore($actions, string $modelClass): void
    {
        if (!$this->shouldAttach()) {
            return;
        }

        $actions->append(new Restore($this->model($modelClass)));
    }

    public function attachBatchRestore($batch, string $modelClass): void
    {
        if (!$this->shouldAttach()) {
            return;
        }

        $batch->add(new BatchRestore($this->model($modelClass)));
    }
}
