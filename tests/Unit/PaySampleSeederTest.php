<?php

namespace Tests\Unit;

use App\Models\Pay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

require_once __DIR__ . '/../../database/seeds/PaySampleSeeder.php';

class PaySampleSeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_payment_sample_seeder_upserts_gateway_examples(): void
    {
        Pay::withTrashed()->forceDelete();

        $seeder = new \PaySampleSeeder();
        $seeder->run();

        $this->assertSame(7, Pay::query()->count());
        $this->assertNotNull(Pay::query()->where('pay_check', 'zfbf2f')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'aliweb')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'wescan')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'alipay')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'wxpay')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'qqpay')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'epusdt')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'paypal')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'stripe')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'coinbase')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'mqq')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'tokenpay-trx')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'pszfb')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'payjswescan')->first());
        $this->assertNull(Pay::query()->where('pay_check', 'vzfb')->first());
        $this->assertSame('/pay/alipay', Pay::query()->where('pay_check', 'zfbf2f')->value('pay_handleroute'));
        $this->assertSame('/pay/wepay', Pay::query()->where('pay_check', 'wescan')->value('pay_handleroute'));
        $this->assertSame('/pay/yipay', Pay::query()->where('pay_check', 'wxpay')->value('pay_handleroute'));
        $this->assertSame('/pay/epusdt', Pay::query()->where('pay_check', 'epusdt')->value('pay_handleroute'));
    }
}
