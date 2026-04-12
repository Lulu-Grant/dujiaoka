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
        $header = $this->buildResourceHeader('共 '.$sections->total().' 个配置分组 · 基础 / 通知 / 行为 / 邮件 / 体验');
        $header['actions'][] = [
            'label' => '编辑品牌与 Logo 配置',
            'href' => admin_url('v2/system-setting/branding'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '编辑基础站点配置',
            'href' => admin_url('v2/system-setting/base'),
            'variant' => 'primary',
        ];
        $header['actions'][] = [
            'label' => '编辑邮件配置',
            'href' => admin_url('v2/system-setting/mail'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '编辑订单行为配置',
            'href' => admin_url('v2/system-setting/order'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '编辑通知推送配置',
            'href' => admin_url('v2/system-setting/push'),
            'variant' => 'secondary',
        ];
        $header['actions'][] = [
            'label' => '编辑站点体验配置',
            'href' => admin_url('v2/system-setting/experience'),
            'variant' => 'secondary',
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

    public function buildShowHeader(?string $scope = null, ?array $section = null): array
    {
        $header = $this->buildResourceShowHeader();

        if ($section) {
            $header['meta'] = sprintf('%s · %d 个配置项', $section['summary'], count($section['items']));
        }

        return $header;
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
            $this->buildShowHeader($scope, $section),
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
                'summary' => '品牌、模板、语言、管理邮箱与 SEO 信息',
                'item_count' => 9,
                'items' => [
                    ['label' => '站点标题', 'value' => $settings['title'] ?? ''],
                    ['label' => '图片 Logo', 'value' => $settings['img_logo'] ?? ''],
                    ['label' => '文字 Logo', 'value' => $settings['text_logo'] ?? ''],
                    ['label' => '主题模板', 'value' => $settings['template'] ?? ''],
                    ['label' => '默认语言', 'value' => $settings['language'] ?? ''],
                    ['label' => '管理邮箱', 'value' => $settings['manage_email'] ?? ''],
                    ['label' => '站点关键字', 'value' => $settings['keywords'] ?? ''],
                    ['label' => '站点描述', 'value' => $settings['description'] ?? ''],
                    ['label' => '站点公告', 'value' => $settings['notice'] ?? '', 'style' => 'grid-column: 1 / -1;', 'value_style' => 'white-space: pre-wrap;'],
                ],
            ],
            [
                'id' => 2,
                'title' => '通知推送配置',
                'summary' => 'Server 酱、Telegram、Bark 与企业微信推送',
                'item_count' => 11,
                'items' => [
                    ['label' => 'Server 酱推送', 'value' => $this->statusText($settings['is_open_server_jiang'] ?? 0)],
                    ['label' => 'Server 酱 Token', 'value' => $settings['server_jiang_token'] ?? ''],
                    ['label' => 'Telegram 推送', 'value' => $this->statusText($settings['is_open_telegram_push'] ?? 0)],
                    ['label' => 'Telegram Bot Token', 'value' => $settings['telegram_bot_token'] ?? ''],
                    ['label' => 'Telegram 用户 ID', 'value' => $settings['telegram_userid'] ?? ''],
                    ['label' => 'Bark 推送', 'value' => $this->statusText($settings['is_open_bark_push'] ?? 0)],
                    ['label' => 'Bark 自定义地址', 'value' => $this->statusText($settings['is_open_bark_push_url'] ?? 0)],
                    ['label' => 'Bark 服务器', 'value' => $settings['bark_server'] ?? ''],
                    ['label' => 'Bark Token', 'value' => $settings['bark_token'] ?? ''],
                    ['label' => '企业微信机器人', 'value' => $this->statusText($settings['is_open_qywxbot_push'] ?? 0)],
                    ['label' => '企业微信机器人 Key', 'value' => $settings['qywxbot_key'] ?? ''],
                ],
            ],
            [
                'id' => 3,
                'title' => '订单行为配置',
                'summary' => '订单过期时间、图形验证码与查询密码',
                'item_count' => 3,
                'items' => [
                    ['label' => '订单过期时间', 'value' => (string) ($settings['order_expire_time'] ?? '')],
                    ['label' => '图片验证码', 'value' => $this->statusText($settings['is_open_img_code'] ?? 0)],
                    ['label' => '订单查询密码', 'value' => $this->statusText($settings['is_open_search_pwd'] ?? 0)],
                ],
            ],
            [
                'id' => 4,
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
            [
                'id' => 5,
                'title' => '站点体验配置',
                'summary' => '前台防红、验证码、搜索密码和文案展示',
                'item_count' => 4,
                'items' => [
                    ['label' => '微信 / QQ 防红', 'value' => $this->statusText($settings['is_open_anti_red'] ?? 0)],
                    ['label' => 'Google 翻译', 'value' => $this->statusText($settings['is_open_google_translate'] ?? 0)],
                    ['label' => '站点公告', 'value' => $settings['notice'] ?? '', 'style' => 'grid-column: 1 / -1;', 'value_style' => 'white-space: pre-wrap;'],
                    ['label' => '页脚自定义代码', 'value' => $settings['footer'] ?? '', 'style' => 'grid-column: 1 / -1;', 'value_style' => 'white-space: pre-wrap;'],
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
