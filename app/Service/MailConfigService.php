<?php

namespace App\Service;

class MailConfigService
{
    /**
     * @var SystemSettingService
     */
    private $systemSettingService;

    public function __construct(SystemSettingService $systemSettingService)
    {
        $this->systemSettingService = $systemSettingService;
    }

    public function runtimeConfig(): array
    {
        return [
            'driver' => $this->systemSettingService->get('driver', 'smtp'),
            'host' => $this->systemSettingService->get('host', ''),
            'port' => $this->systemSettingService->get('port', 587),
            'username' => $this->systemSettingService->get('username', ''),
            'from' => [
                'address' => $this->systemSettingService->get('from_address', ''),
                'name' => $this->systemSettingService->get('from_name', '独角数卡西瓜版'),
            ],
            'password' => $this->systemSettingService->get('password', ''),
            'encryption' => $this->systemSettingService->get('encryption', ''),
        ];
    }
}
