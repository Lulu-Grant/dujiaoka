<?php

namespace App\Http\Controllers\AdminShell;

use App\Http\Controllers\Controller;
use App\Models\BaseModel;
use App\Service\SystemSettingService;
use Illuminate\Http\Request;

class SystemSettingActionController extends Controller
{
    /**
     * @var \App\Service\SystemSettingService
     */
    private $systemSettingService;

    public function __construct(SystemSettingService $systemSettingService)
    {
        $this->systemSettingService = $systemSettingService;
    }

    public function editBase()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-base', $this->actionPageData(
            '编辑基础站点配置 - 后台壳样板',
            '编辑基础站点配置',
            '这是后台壳中的基础站点配置页。当前把品牌展示、基础信息和订单节奏拆成独立分组，方便按用途快速定位。',
            '当前为过渡样板，后续可继续细分品牌展示、SEO 与订单节奏入口',
            admin_url('v2/system-setting/base'),
            [
                $this->section(
                    '品牌与展示',
                    '站点标题、Logo、模板和语言，决定首页的第一印象。',
                    [
                        $this->field('站点标题', 'title', $settings['title'] ?? '', 'text', ['required' => true]),
                        $this->field('文字 Logo', 'text_logo', $settings['text_logo'] ?? ''),
                        $this->field('图片 Logo 路径', 'img_logo', $settings['img_logo'] ?? ''),
                        $this->field('主题模板', 'template', $settings['template'] ?? 'avatar', 'select', ['options' => config('dujiaoka.templates', []), 'required' => true]),
                        $this->field('默认语言', 'language', $settings['language'] ?? 'zh_CN', 'select', ['options' => config('dujiaoka.language', []), 'required' => true]),
                    ],
                    '如果只改首页视觉风格，优先看这一组。'
                ),
                $this->section(
                    '运营与收录',
                    '管理邮箱和 SEO 文案，方便站点维护与搜索收录。',
                    [
                        $this->field('管理邮箱', 'manage_email', $settings['manage_email'] ?? '', 'email'),
                        $this->field('站点关键字', 'keywords', $settings['keywords'] ?? ''),
                        $this->field('站点描述', 'description', $settings['description'] ?? '', 'textarea', ['rows' => 6, 'wide' => true]),
                    ]
                ),
                $this->section(
                    '订单节奏',
                    '订单过期时间仍保留在这里，便于快速调节购买链路节奏。',
                    [
                        $this->field('订单过期时间（分钟）', 'order_expire_time', $settings['order_expire_time'] ?? 5, 'number', ['min' => 1, 'max' => 1440, 'required' => true]),
                    ]
                ),
            ],
            [
                'defaults' => [
                    'title' => $settings['title'] ?? '',
                    'text_logo' => $settings['text_logo'] ?? '',
                    'template' => $settings['template'] ?? 'avatar',
                    'language' => $settings['language'] ?? 'zh_CN',
                    'manage_email' => $settings['manage_email'] ?? '',
                    'order_expire_time' => $settings['order_expire_time'] ?? 5,
                    'keywords' => $settings['keywords'] ?? '',
                    'description' => $settings['description'] ?? '',
                ],
                'templateOptions' => config('dujiaoka.templates', []),
                'languageOptions' => config('dujiaoka.language', []),
            ],
            '保存基础站点配置'
        ));
    }

    public function editBranding()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-branding', $this->actionPageData(
            '编辑品牌与 Logo 配置 - 后台壳样板',
            '编辑品牌与 Logo 配置',
            '这是后台壳中的品牌配置样板页。当前独立承接站点标题、文字 Logo 与图片 Logo 路径配置，便于后续继续扩展媒体资源管理。',
            '当前先使用文本路径方式维护图片 Logo，后续若需要可再接入专门的媒体上传壳',
            admin_url('v2/system-setting/branding'),
            [
                $this->section(
                    '品牌标识',
                    '站点标题、文字 Logo 与图片 Logo 一起决定品牌识别度。',
                    [
                        $this->field('站点标题', 'title', $settings['title'] ?? '', 'text', ['required' => true]),
                        $this->field('文字 Logo', 'text_logo', $settings['text_logo'] ?? ''),
                        $this->field('图片 Logo 路径', 'img_logo', $settings['img_logo'] ?? ''),
                    ],
                    '如果只想替换 Logo 或站点标题，优先在这一组完成。'
                ),
                $this->section(
                    '主题与语言',
                    '模板和语言决定前台的外观与默认文案语言。',
                    [
                        $this->field('默认主题', 'template', $settings['template'] ?? 'avatar', 'select', ['options' => config('dujiaoka.templates', []), 'required' => true]),
                        $this->field('默认语言', 'language', $settings['language'] ?? 'zh_CN', 'select', ['options' => config('dujiaoka.language', []), 'required' => true]),
                    ]
                ),
            ],
            [
                'defaults' => [
                    'title' => $settings['title'] ?? '',
                    'text_logo' => $settings['text_logo'] ?? '',
                    'img_logo' => $settings['img_logo'] ?? '',
                    'template' => $settings['template'] ?? 'avatar',
                    'language' => $settings['language'] ?? 'zh_CN',
                ],
                'templateOptions' => config('dujiaoka.templates', []),
                'languageOptions' => config('dujiaoka.language', []),
            ],
            '保存品牌配置'
        ));
    }

    public function updateBase(Request $request)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'text_logo' => ['nullable', 'string', 'max:255'],
            'template' => ['required', 'string'],
            'language' => ['required', 'string'],
            'manage_email' => ['nullable', 'email'],
            'order_expire_time' => ['required', 'integer', 'min:1', 'max:1440'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/base'))
            ->with('status', '基础站点配置已保存');
    }

    public function updateBranding(Request $request)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'text_logo' => ['nullable', 'string', 'max:255'],
            'img_logo' => ['nullable', 'string', 'max:1000'],
            'template' => ['required', 'string'],
            'language' => ['required', 'string'],
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/branding'))
            ->with('status', '品牌与 Logo 配置已保存');
    }

    public function editMail()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-mail', $this->actionPageData(
            '编辑邮件配置 - 后台壳样板',
            '编辑邮件配置',
            '这是后台壳中的邮件配置编辑样板页。当前先承接 SMTP 相关字段，继续验证后台壳对真实配置保存的能力。',
            '当前为过渡样板，后续可继续扩展通知与推送配置编辑',
            admin_url('v2/system-setting/mail'),
            [
                $this->section(
                    'SMTP 连接',
                    '邮件服务器连接与认证信息集中放在这一组。',
                    [
                        $this->field('邮件驱动', 'driver', $settings['driver'] ?? 'smtp'),
                        $this->field('SMTP 主机', 'host', $settings['host'] ?? ''),
                        $this->field('SMTP 端口', 'port', $settings['port'] ?? 587, 'number', ['min' => 1, 'max' => 65535]),
                        $this->field('账号', 'username', $settings['username'] ?? ''),
                        $this->field('密码', 'password', $settings['password'] ?? ''),
                        $this->field('协议', 'encryption', $settings['encryption'] ?? ''),
                    ],
                    '如果邮件发不出去，先确认这组字段。'
                ),
                $this->section(
                    '发件身份',
                    '发件地址和显示名称决定邮件到达后的可识别度。',
                    [
                        $this->field('发件地址', 'from_address', $settings['from_address'] ?? '', 'email'),
                        $this->field('发件名称', 'from_name', $settings['from_name'] ?? '独角数卡西瓜版'),
                    ]
                ),
            ],
            [
                'defaults' => [
                    'driver' => $settings['driver'] ?? 'smtp',
                    'host' => $settings['host'] ?? '',
                    'port' => $settings['port'] ?? 587,
                    'username' => $settings['username'] ?? '',
                    'password' => $settings['password'] ?? '',
                    'encryption' => $settings['encryption'] ?? '',
                    'from_address' => $settings['from_address'] ?? '',
                    'from_name' => $settings['from_name'] ?? '独角数卡西瓜版',
                ],
            ],
            '保存邮件配置'
        ));
    }

    public function editOrder()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-order', $this->actionPageData(
            '编辑订单行为配置 - 后台壳样板',
            '编辑订单行为配置',
            '这是后台壳中的订单行为配置样板页。当前先承接订单过期、订单查询密码和图形验证码等订单链路相关配置，验证后台壳对业务行为配置保存的能力。',
            '当前为过渡样板，后续可继续扩展更多订单风控和结算行为配置',
            admin_url('v2/system-setting/order'),
            [
                $this->section(
                    '订单行为',
                    '这里只承接订单链路最直接的控制项。',
                    [
                        $this->field('订单过期时间（分钟）', 'order_expire_time', $settings['order_expire_time'] ?? 5, 'number', ['min' => 1, 'max' => 1440, 'required' => true]),
                        $this->field('开启图形验证码', 'is_open_img_code', (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('开启订单查询密码', 'is_open_search_pwd', (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                    ],
                    '这组字段影响订单生成后的查单与到期节奏。'
                ),
            ],
            [
                'defaults' => [
                    'order_expire_time' => $settings['order_expire_time'] ?? 5,
                    'is_open_img_code' => (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE),
                    'is_open_search_pwd' => (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE),
                ],
            ],
            '保存订单行为配置'
        ));
    }

    public function updateMail(Request $request)
    {
        $payload = $request->validate([
            'driver' => ['required', 'string', 'max:50'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'string', 'max:50'],
            'from_address' => ['nullable', 'email'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/mail'))
            ->with('status', '邮件配置已保存');
    }

    public function updateOrder(Request $request)
    {
        $payload = $request->validate([
            'order_expire_time' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);

        $payload = array_merge($payload, [
            'is_open_img_code' => $request->boolean('is_open_img_code') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_search_pwd' => $request->boolean('is_open_search_pwd') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/order'))
            ->with('status', '订单行为配置已保存');
    }

    public function editPush()
    {
        $settings = $this->systemSettingService->all();

        return view('admin-shell.system-setting.edit-push', $this->actionPageData(
            '编辑通知推送配置 - 后台壳样板',
            '编辑通知推送配置',
            '这是后台壳中的通知推送配置样板页。当前先承接订单通知与推送相关字段，继续验证后台壳对真实配置保存的能力。',
            '当前为过渡样板，后续可继续补更细的通知分组和运行时诊断入口',
            admin_url('v2/system-setting/push'),
            [
                $this->section(
                    'Server 酱',
                    'Server 酱是最轻量的运维通知入口，适合先确认基础推送链路。',
                    [
                        $this->field('开启 Server 酱推送', 'is_open_server_jiang', (int) ($settings['is_open_server_jiang'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_server_jiang'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('Server 酱 Token', 'server_jiang_token', $settings['server_jiang_token'] ?? ''),
                    ],
                    '这一组只负责最轻量的服务端通知。'
                ),
                $this->section(
                    'Telegram',
                    'Telegram 需要 Bot Token 和用户 ID 才能完成推送。',
                    [
                        $this->field('开启 Telegram 推送', 'is_open_telegram_push', (int) ($settings['is_open_telegram_push'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_telegram_push'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('Telegram Bot Token', 'telegram_bot_token', $settings['telegram_bot_token'] ?? ''),
                        $this->field('Telegram 用户 ID', 'telegram_userid', $settings['telegram_userid'] ?? ''),
                    ]
                ),
                $this->section(
                    'Bark',
                    'Bark 可同时控制通知开关和回调 URL 输出。',
                    [
                        $this->field('开启 Bark 推送', 'is_open_bark_push', (int) ($settings['is_open_bark_push'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_bark_push'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('推送订单 URL', 'is_open_bark_push_url', (int) ($settings['is_open_bark_push_url'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_bark_push_url'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('Bark 服务器', 'bark_server', $settings['bark_server'] ?? ''),
                        $this->field('Bark Token', 'bark_token', $settings['bark_token'] ?? ''),
                    ]
                ),
                $this->section(
                    '企业微信机器人',
                    '企业微信机器人用于企业内部通知，适合和其他推送并行配置。',
                    [
                        $this->field('开启企业微信机器人推送', 'is_open_qywxbot_push', (int) ($settings['is_open_qywxbot_push'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_qywxbot_push'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('企业微信机器人 Key', 'qywxbot_key', $settings['qywxbot_key'] ?? ''),
                    ]
                ),
            ],
            [
                'defaults' => [
                    'is_open_server_jiang' => (int) ($settings['is_open_server_jiang'] ?? BaseModel::STATUS_CLOSE),
                    'server_jiang_token' => $settings['server_jiang_token'] ?? '',
                    'is_open_telegram_push' => (int) ($settings['is_open_telegram_push'] ?? BaseModel::STATUS_CLOSE),
                    'telegram_bot_token' => $settings['telegram_bot_token'] ?? '',
                    'telegram_userid' => $settings['telegram_userid'] ?? '',
                    'is_open_bark_push' => (int) ($settings['is_open_bark_push'] ?? BaseModel::STATUS_CLOSE),
                    'is_open_bark_push_url' => (int) ($settings['is_open_bark_push_url'] ?? BaseModel::STATUS_CLOSE),
                    'bark_server' => $settings['bark_server'] ?? '',
                    'bark_token' => $settings['bark_token'] ?? '',
                    'is_open_qywxbot_push' => (int) ($settings['is_open_qywxbot_push'] ?? BaseModel::STATUS_CLOSE),
                    'qywxbot_key' => $settings['qywxbot_key'] ?? '',
                ],
            ],
            '保存通知推送配置'
        ));
    }

    public function updatePush(Request $request)
    {
        $payload = $request->validate([
            'server_jiang_token' => ['nullable', 'string', 'max:255'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_userid' => ['nullable', 'string', 'max:255'],
            'bark_server' => ['nullable', 'string', 'max:255'],
            'bark_token' => ['nullable', 'string', 'max:255'],
            'qywxbot_key' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = array_merge($payload, [
            'is_open_server_jiang' => $request->boolean('is_open_server_jiang') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_telegram_push' => $request->boolean('is_open_telegram_push') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_bark_push' => $request->boolean('is_open_bark_push') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_bark_push_url' => $request->boolean('is_open_bark_push_url') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_qywxbot_push' => $request->boolean('is_open_qywxbot_push') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/push'))
            ->with('status', '通知推送配置已保存');
    }

    public function editExperience(Request $request)
    {
        $settings = $this->systemSettingService->all();

        if ($request->query('mode') === 'payment') {
            return view('admin-shell.system-setting.edit-payment', $this->actionPageData(
                '编辑支付与查单配置 - 后台壳样板',
                '编辑支付与查单配置',
                '这是后台壳中的支付与查单节奏配置样板页。当前先承接订单过期时间、图形验证码和查询密码这些低风险字段，方便从支付侧排查购买链路节奏。',
                '当前为过渡样板，后续可继续扩展支付节奏与查单体验的更细分入口',
                admin_url('v2/system-setting/experience').'?mode=payment',
                [
                    $this->section(
                        '支付与查单节奏',
                        '订单过期时间和查单保护项放在这一组，属于最轻量的支付相关体验配置。',
                        [
                            $this->field('订单过期时间（分钟）', 'order_expire_time', $settings['order_expire_time'] ?? 5, 'number', ['min' => 1, 'max' => 1440, 'required' => true]),
                            $this->field('开启图形验证码', 'is_open_img_code', (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                            $this->field('开启订单查询密码', 'is_open_search_pwd', (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        ],
                        '如果你要调购买后查单节奏，优先从这里改。'
                    ),
                ],
                [
                    'defaults' => [
                        'order_expire_time' => $settings['order_expire_time'] ?? 5,
                        'is_open_img_code' => (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE),
                        'is_open_search_pwd' => (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE),
                    ],
                ],
                '保存支付与查单配置'
            ));
        }

        return view('admin-shell.system-setting.edit-experience', $this->actionPageData(
            '编辑站点体验配置 - 后台壳样板',
            '编辑站点体验配置',
            '这是后台壳中的站点体验配置样板页。当前先承接前台搜索密码、验证码、翻译、防红、公告和页脚等低风险字段。',
            '当前为过渡样板，后续可继续扩展公告、SEO 和模板资源等更多展示型配置',
            admin_url('v2/system-setting/experience'),
            [
                $this->section(
                    '访问风控',
                    '前台开关类配置集中在这一组，优先影响页面访问体验。',
                    [
                        $this->field('开启微信 / QQ 防红', 'is_open_anti_red', (int) ($settings['is_open_anti_red'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_anti_red'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('开启图形验证码', 'is_open_img_code', (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('开启订单查询密码', 'is_open_search_pwd', (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                        $this->field('开启 Google 翻译', 'is_open_google_translate', (int) ($settings['is_open_google_translate'] ?? BaseModel::STATUS_CLOSE), 'checkbox', ['checked' => (int) ($settings['is_open_google_translate'] ?? BaseModel::STATUS_CLOSE) === BaseModel::STATUS_OPEN]),
                    ],
                    '如果用户访问体验受影响，先从这里排查。'
                ),
                $this->section(
                    '文案与页脚',
                    '站点公告和页脚代码决定前台的信息表达和收尾方式。',
                    [
                        $this->field('站点公告', 'notice', $settings['notice'] ?? '', 'textarea', ['rows' => 8, 'wide' => true]),
                        $this->field('页脚自定义代码', 'footer', $settings['footer'] ?? '', 'textarea', ['rows' => 8, 'wide' => true]),
                    ]
                ),
            ],
            [
                'defaults' => [
                    'is_open_anti_red' => (int) ($settings['is_open_anti_red'] ?? BaseModel::STATUS_CLOSE),
                    'is_open_img_code' => (int) ($settings['is_open_img_code'] ?? BaseModel::STATUS_CLOSE),
                    'is_open_search_pwd' => (int) ($settings['is_open_search_pwd'] ?? BaseModel::STATUS_CLOSE),
                    'is_open_google_translate' => (int) ($settings['is_open_google_translate'] ?? BaseModel::STATUS_CLOSE),
                    'notice' => $settings['notice'] ?? '',
                    'footer' => $settings['footer'] ?? '',
                ],
            ],
            '保存站点体验配置'
        ));
    }

    public function updateExperience(Request $request)
    {
        if ($request->query('mode') === 'payment') {
            $payload = $request->validate([
                'order_expire_time' => ['required', 'integer', 'min:1', 'max:1440'],
            ]);

            $payload = array_merge($payload, [
                'is_open_img_code' => $request->boolean('is_open_img_code') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
                'is_open_search_pwd' => $request->boolean('is_open_search_pwd') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            ]);

            $this->systemSettingService->save($payload);

            return redirect(admin_url('v2/system-setting/experience').'?mode=payment')
                ->with('status', '支付与查单配置已保存');
        }

        $payload = $request->validate([
            'notice' => ['nullable', 'string', 'max:20000'],
            'footer' => ['nullable', 'string', 'max:20000'],
        ]);

        $payload = array_merge($payload, [
            'is_open_anti_red' => $request->boolean('is_open_anti_red') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_img_code' => $request->boolean('is_open_img_code') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_search_pwd' => $request->boolean('is_open_search_pwd') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
            'is_open_google_translate' => $request->boolean('is_open_google_translate') ? BaseModel::STATUS_OPEN : BaseModel::STATUS_CLOSE,
        ]);

        $this->systemSettingService->save($payload);

        return redirect(admin_url('v2/system-setting/experience'))
            ->with('status', '站点体验配置已保存');
    }

    private function actionPageData(
        string $title,
        string $heading,
        string $description,
        string $meta,
        string $formAction,
        array $sections,
        array $extra = [],
        string $submitLabel = '保存配置'
    ): array {
        return array_merge([
            'title' => $title,
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => $heading,
                'description' => $description,
                'meta' => $meta,
                'actions' => [
                    ['label' => '返回系统设置概览', 'href' => admin_url('v2/system-setting')],
                ],
            ],
            'formAction' => $formAction,
            'submitLabel' => $submitLabel,
            'sections' => $sections,
        ], $extra);
    }

    private function section(string $title, string $description, array $fields, ?string $note = null): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'note' => $note,
            'fields' => $fields,
        ];
    }

    private function field(string $label, string $name, $value, string $type = 'text', array $options = []): array
    {
        return array_merge([
            'label' => $label,
            'name' => $name,
            'value' => $value,
            'type' => $type,
        ], $options);
    }
}
