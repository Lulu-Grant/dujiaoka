<?php

namespace App\Service;

use App\Models\Emailtpl;
use App\Service\Contracts\AdminShellPageServiceInterface;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellEmailTemplatePageService implements AdminShellPageServiceInterface
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

    public function extractFilters(Request $request): array
    {
        return [
            'id' => $request->query('id'),
            'tpl_name' => $request->query('tpl_name'),
            'tpl_token' => $request->query('tpl_token'),
        ];
    }

    public function find(int $id, ?string $scope = null): Emailtpl
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
                    $this->renderActionLinks([
                        ['label' => '查看详情', 'href' => admin_url('v2/emailtpl/'.$template->id)],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有邮件模板记录。',
            'empty_description' => '可以调整邮件标题或模板标识筛选条件，继续查找需要的模板。',
            'paginator' => $templates,
        ];
    }

    public function buildHeader(LengthAwarePaginator $templates): array
    {
        return [
            'title' => '邮件模板管理',
            'description' => '这是第二张后台壳样板页。当前列表、筛选和详情都通过普通 Laravel 控制器与 Blade 组合，不再依赖 Dcat Grid/Show。',
            'meta' => '共 '.$templates->total().' 条模板',
            'actions' => [
                [
                    'label' => '迁移合同',
                    'href' => 'https://github.com/Lulu-Grant/dujiaoka/blob/master/docs/admin-first-batch-migration-contracts.md',
                    'variant' => 'secondary',
                ],
            ],
        ];
    }

    public function buildFilters(array $filters): array
    {
        return [
            'fields' => [
                ['label' => 'ID', 'name' => 'id', 'type' => 'number', 'value' => $filters['id'] ?? null],
                ['label' => '邮件标题', 'name' => 'tpl_name', 'value' => $filters['tpl_name'] ?? null],
                ['label' => '邮件标识', 'name' => 'tpl_token', 'value' => $filters['tpl_token'] ?? null],
            ],
            'resetUrl' => admin_url('v2/emailtpl'),
        ];
    }

    public function buildShowHeader(): array
    {
        return [
            'title' => '邮件模板详情',
            'description' => '这张详情页用于固定邮件模板的字段合同，后续新后台壳可以直接复用。',
            'actions' => [
                ['label' => '返回列表', 'href' => admin_url('v2/emailtpl')],
            ],
        ];
    }

    public function buildIndexPageData(LengthAwarePaginator $templates, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            '邮件模板管理 - 后台壳样板',
            $this->buildHeader($templates),
            $this->buildFilters($filters),
            $this->buildTable($templates)
        );
    }

    public function buildShowPageData($template, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            '邮件模板详情 - 后台壳样板',
            $this->buildShowHeader(),
            $this->detailItems($template)
        );
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

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
