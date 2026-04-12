<?php

namespace App\Service;

use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Http\Request;

class AdminShellSystemSettingPageService extends AbstractAdminShellPageService
{
    /**
     * @var \App\Service\SystemSettingService
     */
    private $systemSettingService;

    /**
     * @var \App\Service\MailConfigService
     */
    private $mailConfigService;

    public function __construct(
        AdminShellResourceRegistry $resourceRegistry,
        SystemSettingService $systemSettingService,
        MailConfigService $mailConfigService
    ) {
        parent::__construct($resourceRegistry);
        $this->systemSettingService = $systemSettingService;
        $this->mailConfigService = $mailConfigService;
    }

    public function extractFilters(Request $request): array
    {
        return [
            'section' => $request->query('section'),
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sections = collect($this->sections());

        if (!empty($filters['section'])) {
            $keyword = $filters['section'];
            $sections = $sections->filter(function (array $section) use ($keyword) {
                return mb_stripos($section['title'], $keyword) !== false;
            })->values();
        }

        return new Paginator(
            $sections->all(),
            $sections->count(),
            15,
            1,
            ['path' => admin_url($this->resourceDefinition()['uri']), 'query' => array_filter($filters)]
        );
    }

    public function find(int $id, ?string $scope = null)
    {
        $section = collect($this->sections())->first(function (array $section) use ($id) {
            return $section['id'] === $id;
        });

        abort_if(!$section, 404, 'Unknown system setting section.');

        return $section;
    }

    public function buildTable(LengthAwarePaginator $sections): array
    {
        $definition = $this->resourceDefinition();

        return [
            'headers' => ['ID', '配置分组', '说明', '配置项数', '操作'],
            'rows' => collect($sections->items())->map(function (array $section) use ($definition) {
                return [
                    $section['id'],
                    e($section['title']),
                    e($section['summary']),
                    $section['item_count'],
                    $this->renderActionLinks([
                        [
                            'label' => '查看详情',
                            'href' => admin_url($definition['uri'].'/'.$section['id']),
                        ],
                    ]),
                ];
            })->all(),
            'empty_title' => '当前条件下没有系统设置分组。',
            'empty_description' => '可以调整分组关键字筛选条件，继续查找设置分组。',
            'paginator' => $sections,
        ];
    }

    public function buildHeader(LengthAwarePaginator $sections): array
    {
        $header = $this->buildResourceHeader('共 '.$sections->total().' 个配置分组');
        $header['actions'][] = [
            'label' => '编辑基础站点配置',
            'href' => admin_url('v2/system-setting/base'),
            'variant' => 'primary',
        ];

        return $header;
    }

    public function buildFilters(array $filters): array
    {
        $definition = $this->resourceDefinition();

        return [
            'fields' => [
                ['label' => '分组关键字', 'name' => 'section', 'value' => $filters['section'] ?? null],
            ],
            'resetUrl' => admin_url($definition['uri']),
        ];
    }

    public function buildShowHeader(?string $scope = null): array
    {
        return $this->buildResourceShowHeader();
    }

    public function buildIndexPageData(LengthAwarePaginator $sections, array $filters): AdminShellIndexPageData
    {
        return new AdminShellIndexPageData(
            $this->buildDocumentTitle('index_title'),
            $this->buildHeader($sections),
            $this->buildFilters($filters),
            $this->buildTable($sections)
        );
    }

    public function buildShowPageData($section, ?string $scope = null): AdminShellShowPageData
    {
        return new AdminShellShowPageData(
            $this->buildDocumentTitle('show_title'),
            $this->buildShowHeader(),
            $this->detailItems($section)
        );
    }

    public function detailItems($section): array
    {
        return collect($section['items'])->map(function (array $item) {
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
        return 'system-setting';
    }

    private function sections(): array
    {
        $settings = $this->systemSettingService->all();
        $mail = $this->mailConfigService->runtimeConfig();

        return [
            [
                'id' => 1,
                'title' => '基础站点配置',
                'summary' => '品牌、模板、语言与站点展示信息',
                'item_count' => 8,
                'items' => [
                    ['label' => '站点标题', 'value' => $settings['title'] ?? ''],
                    ['label' => '文字 Logo', 'value' => $settings['text_logo'] ?? ''],
                    ['label' => '主题模板', 'value' => $settings['template'] ?? ''],
                    ['label' => '默认语言', 'value' => $settings['language'] ?? ''],
                    ['label' => '站点关键字', 'value' => $settings['keywords'] ?? ''],
                    ['label' => '站点描述', 'value' => $settings['description'] ?? ''],
                    ['label' => '站点公告', 'value' => $settings['notice'] ?? '', 'style' => 'grid-column: 1 / -1;', 'value_style' => 'white-space: pre-wrap;'],
                    ['label' => '页脚文案', 'value' => $settings['footer'] ?? '', 'style' => 'grid-column: 1 / -1;', 'value_style' => 'white-space: pre-wrap;'],
                ],
            ],
            [
                'id' => 2,
                'title' => '订单与通知配置',
                'summary' => '订单过期、搜索密码与通知推送开关',
                'item_count' => 10,
                'items' => [
                    ['label' => '订单过期时间', 'value' => (string) ($settings['order_expire_time'] ?? '')],
                    ['label' => '反红开关', 'value' => $this->statusText($settings['is_open_anti_red'] ?? 0)],
                    ['label' => '图片验证码', 'value' => $this->statusText($settings['is_open_img_code'] ?? 0)],
                    ['label' => '搜索密码', 'value' => $this->statusText($settings['is_open_search_pwd'] ?? 0)],
                    ['label' => '谷歌翻译', 'value' => $this->statusText($settings['is_open_google_translate'] ?? 0)],
                    ['label' => 'Server 酱推送', 'value' => $this->statusText($settings['is_open_server_jiang'] ?? 0)],
                    ['label' => 'Telegram 推送', 'value' => $this->statusText($settings['is_open_telegram_push'] ?? 0)],
                    ['label' => 'Bark 推送', 'value' => $this->statusText($settings['is_open_bark_push'] ?? 0)],
                    ['label' => 'Bark 自定义地址', 'value' => $this->statusText($settings['is_open_bark_push_url'] ?? 0)],
                    ['label' => '企业微信机器人', 'value' => $this->statusText($settings['is_open_qywxbot_push'] ?? 0)],
                ],
            ],
            [
                'id' => 3,
                'title' => '邮件发送配置',
                'summary' => 'SMTP 驱动、端口、发信身份与连接方式',
                'item_count' => 7,
                'items' => [
                    ['label' => '邮件驱动', 'value' => $mail['driver'] ?? ''],
                    ['label' => 'SMTP 主机', 'value' => $mail['host'] ?? ''],
                    ['label' => 'SMTP 端口', 'value' => (string) ($mail['port'] ?? '')],
                    ['label' => '用户名', 'value' => $mail['username'] ?? ''],
                    ['label' => '加密方式', 'value' => $mail['encryption'] ?? ''],
                    ['label' => '发件地址', 'value' => $mail['from']['address'] ?? ''],
                    ['label' => '发件名称', 'value' => $mail['from']['name'] ?? ''],
                ],
            ],
        ];
    }

    private function statusText($value): string
    {
        return (int) $value === 1 ? '开启' : '关闭';
    }

    private function renderActionLinks(array $actions): string
    {
        return collect($actions)->map(function (array $action) {
            return sprintf('<a href="%s">%s</a>', e($action['href']), e($action['label']));
        })->implode(' / ');
    }
}
