<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Pay;
use App\Service\OrderCheckoutService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderCheckoutServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        config(['dujiaoka.async_side_effects' => true]);
        Queue::fake();
        Cache::put('system-setting', [
            'template' => 'hyper',
            'text_logo' => 'Test Shop',
            'manage_email' => 'admin@example.com',
            'is_open_img_verify' => BaseModel::STATUS_CLOSE,
            'is_open_geetest' => BaseModel::STATUS_CLOSE,
        ]);
    }

    protected function tearDown(): void
    {
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_create_order_from_request_builds_order_through_checkout_service(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Checkout Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Checkout Product',
            'gd_description' => 'Checkout Product Description',
            'gd_keywords' => 'checkout,product',
            'actual_price' => 16.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Checkout Pay',
            'pay_check' => 'checkout-test-pay',
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_pem' => 'secret',
            'pay_handleroute' => '/pay/test',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $request = Request::create('/order', 'POST', [
            'gid' => $goods->id,
            'email' => 'buyer@example.com',
            'payway' => $pay->id,
            'search_pwd' => 'lookup-pass',
            'by_amount' => 2,
            'img_verify_code' => 'ignored',
        ], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $service = app(OrderCheckoutService::class);
        $order = $service->createOrderFromRequest($request);

        $order->refresh();

        $this->assertSame($goods->id, $order->goods_id);
        $this->assertSame($pay->id, $order->pay_id);
        $this->assertSame('Checkout Product x 2', $order->title);
        $this->assertSame('32.00', $order->total_price);
        $this->assertSame('lookup-pass', $order->search_pwd);
    }
}
