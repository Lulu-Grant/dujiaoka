<?php

namespace App\Service;

class AdminFilterService
{
    public function attachTrashedScope($filter): void
    {
        $filter->scope(admin_trans('dujiaoka.trashed'))->onlyTrashed();
    }

    public function applyCreatedAtRange($query, array $input): void
    {
        $start = $input['start'] ?? null;
        $end = $input['end'] ?? null;

        if ($start !== null && $start !== '') {
            $query->where('created_at', '>=', $start);
        }

        if ($end !== null && $end !== '') {
            $query->where('created_at', '<=', $end);
        }
    }
}
