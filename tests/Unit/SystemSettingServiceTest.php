<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Service\SystemSettingService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SystemSettingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget(SystemSettingService::CACHE_KEY);
    }

    public function test_all_merges_defaults_with_cached_values(): void
    {
        Cache::put(SystemSettingService::CACHE_KEY, [
            'title' => '自定义站点',
            'manage_email' => 'admin@example.com',
        ]);

        $service = app(SystemSettingService::class);

        $this->assertSame('自定义站点', $service->get('title'));
        $this->assertSame('avatar', $service->get('template'));
        $this->assertSame(BaseModel::STATUS_CLOSE, $service->get('is_open_img_code'));
    }

    public function test_save_filters_unknown_fields_and_persists_defaults(): void
    {
        $service = app(SystemSettingService::class);

        $settings = $service->save([
            'title' => '设置中心',
            'port' => 2525,
            'unknown_field' => 'should-not-store',
        ]);

        $this->assertSame('设置中心', $settings['title']);
        $this->assertSame(2525, $settings['port']);
        $this->assertArrayNotHasKey('unknown_field', $settings);
        $this->assertSame('avatar', Cache::get(SystemSettingService::CACHE_KEY)['template']);
    }
}
