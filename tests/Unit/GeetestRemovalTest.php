<?php

namespace Tests\Unit;

use App\Exceptions\RuleValidationException;
use App\Models\BaseModel;
use App\Service\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GeetestRemovalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('system-setting', [
            'is_open_search_pwd' => BaseModel::STATUS_CLOSE,
            'is_open_img_code' => BaseModel::STATUS_CLOSE,
            'is_open_geetest' => BaseModel::STATUS_OPEN,
            'is_open_anti_red' => BaseModel::STATUS_CLOSE,
        ]);
    }

    public function test_validator_create_order_ignores_legacy_geetest_switch(): void
    {
        $request = Request::create('/create-order', 'POST', [
            'gid' => 1,
            'email' => 'buyer@example.com',
            'payway' => 1,
            'by_amount' => 1,
            'img_verify_code' => 'ignored',
        ]);

        try {
            app(OrderService::class)->validatorCreateOrder($request);
        } catch (RuleValidationException $exception) {
            $this->assertNotSame(__('dujiaoka.prompt.geetest_validate_fail'), $exception->getMessage());
            return;
        }

        $this->assertTrue(true);
    }
}
