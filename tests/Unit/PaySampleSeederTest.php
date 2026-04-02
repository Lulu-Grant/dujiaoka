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

        $this->assertSame(29, Pay::query()->count());
        $this->assertNotNull(Pay::query()->where('pay_check', 'paypal')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'stripe')->first());
        $this->assertNotNull(Pay::query()->where('pay_check', 'coinbase')->first());
        $this->assertSame('/pay/paypal', Pay::query()->where('pay_check', 'paypal')->value('pay_handleroute'));
        $this->assertSame('pay/tokenpay', Pay::query()->where('pay_check', 'tokenpay-trx')->value('pay_handleroute'));
    }
}
