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

    public function buildTable(LengthAwarePaginator $templates): array
    {
        return [
            'headers' => ['ID', '邮件标题', '邮件标识', '创建时间', '更新时间', '操作'],
            'rows' => $templates->getCollection()->map(function (Emailtpl $template) {
                return [
                    $template->id,
                    e($template->tpl_name),
                    e($template->tpl_token),
                    e((string) $template->created_at),
                    e((string) $template->updated_at),
                    sprintf('<a href="%s">查看详情</a>', e(admin_url('v2/emailtpl/'.$template->id))),
                ];
            })->all(),
            'empty' => '当前条件下没有邮件模板记录。',
            'paginator' => $templates,
        ];
    }

    public function detailItems(Emailtpl $template): array
    {
        return [
            ['label' => 'ID', 'value' => $template->id],
            ['label' => '邮件标题', 'value' => e($template->tpl_name)],
            ['label' => '邮件标识', 'value' => e($template->tpl_token)],
            [
                'label' => '邮件内容',
                'value' => e($template->tpl_content),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; font-size: 14px; font-weight: 500;',
            ],
            ['label' => '创建时间', 'value' => e((string) $template->created_at)],
            ['label' => '更新时间', 'value' => e((string) $template->updated_at)],
        ];
    }
}
