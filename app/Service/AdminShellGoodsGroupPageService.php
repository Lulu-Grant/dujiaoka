<?php

namespace App\Service;

use App\Models\GoodsGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminShellGoodsGroupPageService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = GoodsGroup::query()->withCount('goods')->orderByDesc('id');

        if (($filters['scope'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        }

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        return $query->paginate(15)->appends($filters);
    }

    public function find(int $id, ?string $scope = null): GoodsGroup
    {
        $query = GoodsGroup::query()->withCount('goods');

        if ($scope === 'trashed') {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }
}
