<?php

namespace App\Service;

use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class AdminShellEmailTestPageService extends AbstractAdminShellPageService
{
    /**
     * @var \App\Service\MailConfigService
     */
    private $mailConfigService;

    /**
     * @var \App\Service\SystemSettingService
     */
    private $systemSettingService;

    public function __construct(
        AdminShellResourceRegistry $resourceRegistry,
        MailConfigService $mailConfigService,
        SystemSettingService $systemSettingService
    ) {
        parent::__construct($resourceRegistry);
        $this->mailConfigService = $mailConfigService;
        $this->systemSettingService = $systemSettingService;
    }

    public function extractFilters(Request $request): array
    {
        return [
            'keyword' => $request->query('keyword'),
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $records = collect($this->records());

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $records = $records->filter(function (array $record) use ($keyword) {
                return mb_stripos($record['title'], $keyword) !== false
                    || mb_stripos($record['summary'], $keyword) !== false;
            })->values();
        }

        return new Paginator(
            $records->all(),
            $records->count(),
            15,
            1,
            ['path' => admin_url($this->resourceDefinition()['uri']), 'query' => array_filter($filters)]
        );
    }

    public function find(int $id, ?string $scope = null)
    {
        $record = collect($this->records())->first(function (array $record) use ($id) {
            return $record['id'] === $id;
        });

        abort_if(!$record, 404, 'Unknown email test section.');

        return $record;
    }

    public function buildTable(LengthAwarePaginator $records): array
    {
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '页面分组', '说明', '字段/配置数', '操作'],
            'rows' => collect($records->items())->map(function (array $record) use ($definition) {
                return [
                    $record['id'],
                    e($record['title']),
                    e($record['summary']),
                    $record['item_count'],
                    $this->renderActionLinks([
                        [
                            'label' => '查看详情',
                            'href' => admin_url($definition['uri'].'/'.$record['id']),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有邮件测试页面分组。',
            'empty_description' => '可以调整关键字筛选条件，继续查找邮件测试相关分组。',
            'paginator' => $records,
        ];
    }

    public function buildHeader(LengthAwarePaginator $records): array
    {
        return $this->buildResourceHeader('共 '.$records->total().' 个测试分组');
    }

    public function buildFilters(array $filters): array
    {
        return [
            'fields' => [
                ['label' => '关键字', 'name' => 'keyword', 'value' => $filters['keyword'] ?? null],
            ],
            'resetUrl' => admin_url($this->resourceDefinition()['uri']),
        ];
    }

    public function buildShowHeader(?string $scope = null): array
    {
        return $this->buildResourceShowHeader();
    }

    public function buildIndexPageData(LengthAwarePaginator $records, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($records),
            $this->buildFilters($filters),
            $this->buildTable($records)
        );
    }

    public function buildShowPageData($record, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader(),
            $this->detailItems($record)
        );
    }

    public function detailItems($record): array
    {
        return collect($record['items'])->map(function (array $item) {
            return [
                'label' => $item['label'],
                'value' => e((string) $item['value']),
                'style' => $item['style'] ?? null,
                'value_style' => $item['value_style'] ?? null,
            ];
        })->all();
    }

    protected function resourceKey(): string
    {
        return 'email-test';
    }

    private function records(): array
    {
        $mail = $this->mailConfigService->runtimeConfig();
        $settings = $this->systemSettingService->all();

        return [
            [
                'id' => 1,
                'title' => '测试邮件表单合同',
                'summary' => '用于说明当前邮件测试页预期输入项和默认值',
                'item_count' => 3,
                'items' => [
                    ['label' => '收件人字段', 'value' => 'to'],
                    ['label' => '邮件标题字段', 'value' => 'title'],
                    ['label' => '邮件正文字段', 'value' => 'body'],
                ],
            ],
            [
                'id' => 2,
                'title' => '当前邮件运行时配置',
                'summary' => '展示邮件测试发送时将使用的运行时配置',
                'item_count' => 7,
                'items' => [
                    ['label' => '邮件驱动', 'value' => $mail['driver'] ?? ''],
                    ['label' => 'SMTP 主机', 'value' => $mail['host'] ?? ''],
                    ['label' => 'SMTP 端口', 'value' => (string) ($mail['port'] ?? '')],
                    ['label' => '用户名', 'value' => $mail['username'] ?? ''],
                    ['label' => '加密方式', 'value' => $mail['encryption'] ?? ''],
                    ['label' => '发件地址', 'value' => $mail['from']['address'] ?? ''],
                    ['label' => '发件名称', 'value' => $mail['from']['name'] ?? ($settings['from_name'] ?? '')],
                ],
            ],
        ];
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
