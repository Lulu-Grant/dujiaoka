<?php

namespace App\Service;

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
}
