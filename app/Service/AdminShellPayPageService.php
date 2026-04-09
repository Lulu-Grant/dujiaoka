<?php

namespace App\Service;

use App\Models\Pay;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminShellPayPageService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Pay::query()->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['pay_check'])) {
            $query->where('pay_check', $filters['pay_check']);
        }

        if (!empty($filters['pay_name'])) {
            $query->where('pay_name', 'like', '%'.$filters['pay_name'].'%');
        }

        return $query->paginate(15)->appends($filters);
    }

    public function find(int $id, ?string $scope = null): Pay
    {
        $query = Pay::query();

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }
}
