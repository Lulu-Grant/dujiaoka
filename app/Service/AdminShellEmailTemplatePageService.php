<?php

namespace App\Service;

use App\Models\Emailtpl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminShellEmailTemplatePageService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Emailtpl::query()->orderByDesc('id');

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }

        if (!empty($filters['tpl_name'])) {
            $query->where('tpl_name', 'like', '%'.$filters['tpl_name'].'%');
        }

        if (!empty($filters['tpl_token'])) {
            $query->where('tpl_token', 'like', '%'.$filters['tpl_token'].'%');
        }

        return $query->paginate(15)->appends($filters);
    }

    public function find(int $id): Emailtpl
    {
        return Emailtpl::query()->findOrFail($id);
    }
}
