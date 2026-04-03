<?php

namespace Tests\Unit;

use App\Service\MailConfigService;
use App\Service\SystemSettingService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MailConfigServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::put(SystemSettingService::CACHE_KEY, [
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 1025,
            'username' => 'mailer',
            'password' => 'secret',
            'encryption' => 'tls',
            'from_address' => 'mailer@example.com',
            'from_name' => '独角数卡西瓜版',
        ]);
    }

    public function test_runtime_config_reads_from_system_setting_service(): void
    {
        $config = app(MailConfigService::class)->runtimeConfig();

        $this->assertSame('smtp', $config['driver']);
        $this->assertSame('smtp.example.com', $config['host']);
        $this->assertSame(1025, $config['port']);
        $this->assertSame('mailer@example.com', $config['from']['address']);
        $this->assertSame('独角数卡西瓜版', $config['from']['name']);
        $this->assertSame('tls', $config['encryption']);
    }
}
