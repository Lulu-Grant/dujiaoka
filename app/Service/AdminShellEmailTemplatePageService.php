<?php

namespace App\Service;

use App\Models\Emailtpl;
use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminShellEmailTemplatePageService extends AbstractAdminShellPageService
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
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '邮件标题', '邮件标识', '创建时间', '更新时间', '操作'],
            'rows' => $templates->getCollection()->map(function (Emailtpl $template) use ($definition) {
                return [
                    $template->id,
                    e($template->tpl_name),
                    e($template->tpl_token),
                    e((string) $template->created_at),
                    e((string) $template->updated_at),
                    $this->renderActionLinks([
                        ['label' => '编辑模板', 'href' => admin_url($definition['uri'].'/'.$template->id.'/edit')],
                        ['label' => '查看详情', 'href' => admin_url($definition['uri'].'/'.$template->id)],
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
        $header = $this->buildResourceHeader('共 '.$templates->total().' 条模板 · 模板内容支持 {webname}、{order_id} 等占位符');
        $header['actions'][] = [
            'label' => '新建邮件模板',
            'href' => admin_url('v2/emailtpl/create'),
            'variant' => 'primary',
        ];

        return $header;
    }

    public function buildFilters(array $filters): array
    {
        $definition = $this->resourceDefinition();

        return [
            'fields' => [
                ['label' => 'ID', 'name' => 'id', 'type' => 'number', 'value' => $filters['id'] ?? null],
                ['label' => '邮件标题', 'name' => 'tpl_name', 'value' => $filters['tpl_name'] ?? null],
                ['label' => '邮件标识', 'name' => 'tpl_token', 'value' => $filters['tpl_token'] ?? null],
            ],
            'resetUrl' => admin_url($definition['uri']),
        ];
    }

    public function buildShowHeader(?Emailtpl $template = null): array
    {
        $header = $this->buildResourceShowHeader();

        if ($template) {
            $header['actions'][] = [
                'label' => '编辑模板',
                'href' => admin_url('v2/emailtpl/'.$template->id.'/edit'),
                'variant' => 'secondary',
            ];
        }

        return $header;
    }

    public function buildIndexPageData(LengthAwarePaginator $templates, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($templates),
            $this->buildFilters($filters),
            $this->buildTable($templates)
        );
    }

    public function buildShowPageData($template, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader($template),
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
            [
                'label' => '使用说明',
                'value' => '模板内容支持 {webname}、{order_id}、{ord_info} 等占位符；编辑页右侧会展示实时预览。',
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; font-size: 14px; font-weight: 500;',
            ],
            [
                'label' => '编辑建议',
                'value' => '模板标识创建后建议保持稳定；内容支持 HTML，可直接粘贴并在预览区确认排版。',
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

    protected function resourceKey(): string
    {
        return 'emailtpl';
    }
}
