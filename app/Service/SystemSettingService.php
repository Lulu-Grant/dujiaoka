<?php

namespace App\Service;

use App\Models\BaseModel;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class SystemSettingService
{
    public const CACHE_KEY = 'system-setting';

    /**
     * @var string[]
     */
    private $editableKeys = [
        'title',
        'img_logo',
        'text_logo',
        'keywords',
        'description',
        'template',
        'language',
        'manage_email',
        'order_expire_time',
        'is_open_anti_red',
        'is_open_img_code',
        'is_open_search_pwd',
        'is_open_google_translate',
        'notice',
        'footer',
        'is_open_server_jiang',
        'server_jiang_token',
        'is_open_telegram_push',
        'telegram_bot_token',
        'telegram_userid',
        'is_open_bark_push',
        'is_open_bark_push_url',
        'bark_server',
        'bark_token',
        'is_open_qywxbot_push',
        'qywxbot_key',
        'driver',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
    ];

    /**
     * @var CacheRepository
     */
    private $cache;

    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    public function all(): array
    {
        return array_merge($this->defaults(), $this->stored());
    }

    public function get(string $key, $default = null)
    {
        $settings = $this->all();

        return $settings[$key] ?? $default;
    }

    public function save(array $input): array
    {
        $settings = array_merge(
            $this->stored(),
            $this->filterEditableInput($input)
        );

        $settings = array_merge($this->defaults(), $settings);

        $this->cache->forever(static::CACHE_KEY, $settings);

        return $settings;
    }

    public function defaults(): array
    {
        return [
            'title' => '独角数卡西瓜版',
            'img_logo' => '',
            'text_logo' => '独角数卡西瓜版',
            'keywords' => '',
            'description' => '',
            'template' => 'avatar',
            'language' => 'zh_CN',
            'manage_email' => '',
            'order_expire_time' => 5,
            'is_open_anti_red' => BaseModel::STATUS_CLOSE,
            'is_open_img_code' => BaseModel::STATUS_CLOSE,
            'is_open_search_pwd' => BaseModel::STATUS_CLOSE,
            'is_open_google_translate' => BaseModel::STATUS_CLOSE,
            'notice' => '',
            'footer' => '',
            'is_open_server_jiang' => BaseModel::STATUS_CLOSE,
            'server_jiang_token' => '',
            'is_open_telegram_push' => BaseModel::STATUS_CLOSE,
            'telegram_bot_token' => '',
            'telegram_userid' => '',
            'is_open_bark_push' => BaseModel::STATUS_CLOSE,
            'is_open_bark_push_url' => BaseModel::STATUS_CLOSE,
            'bark_server' => '',
            'bark_token' => '',
            'is_open_qywxbot_push' => BaseModel::STATUS_CLOSE,
            'qywxbot_key' => '',
            'driver' => 'smtp',
            'host' => '',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => '',
            'from_address' => '',
            'from_name' => '独角数卡西瓜版',
        ];
    }

    /**
     * @return string[]
     */
    public function editableKeys(): array
    {
        return $this->editableKeys;
    }

    private function stored(): array
    {
        $settings = $this->cache->get(static::CACHE_KEY, []);

        return is_array($settings) ? $settings : [];
    }

    private function filterEditableInput(array $input): array
    {
        return array_intersect_key($input, array_flip($this->editableKeys()));
    }
}
