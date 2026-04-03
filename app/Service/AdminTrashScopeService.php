<?php

namespace App\Service;

class AdminTrashScopeService
{
    public function isTrashedScope(?string $scope = null): bool
    {
        $scope = $scope ?? request('_scope_');

        return $scope === admin_trans('dujiaoka.trashed');
    }
}
