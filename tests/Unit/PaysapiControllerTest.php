<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\PaysapiController;
use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaysapiControllerTest extends TestCase
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
            'is_open_server_jiang' => BaseModel::STATUS_CLOSE,
            'is_open_telegram_push' => BaseModel::STATUS_CLOSE,
            'is_open_bark_push' => BaseModel::STATUS_CLOSE,
            'is_open_qywxbot_push' => BaseModel::STATUS_CLOSE,
        ]);
    }

    protected function tearDown(): void
    {
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_notify_url_rejects_invalid_signature(): void
    {
        $order = $this->createPaysapiOrder('PAYSAPI-FAIL-001');

        $request = Request::create('/pay/paysapi/notify_url', 'POST', [
            'orderid' => $order->order_sn,
            'orderuid' => $order->email,
            'paysapi_id' => 'TRADE-FAIL',
            'price' => '10',
            'realprice' => '10',
            'key' => 'invalid-signature',
        ]);

        $response = app(PaysapiController::class)->notifyUrl($request);

        $this->assertSame('fail', $response);
    }

    public function test_notify_url_completes_order_with_valid_signature(): void
    {
        $order = $this->createPaysapiOrder('PAYSAPI-SUCCESS-001');
        $payGateway = Pay::query()->findOrFail($order->pay_id);

        $payload = [
            'orderid' => $order->order_sn,
            'orderuid' => $order->email,
            'paysapi_id' => 'TRADE-SUCCESS',
            'price' => '10',
            'realprice' => '10',
        ];
        $payload['key'] = md5(
            $payload['orderid']
            . $payload['orderuid']
            . $payload['paysapi_id']
            . $payload['price']
            . $payload['realprice']
            . $payGateway->merchant_pem
        );

        $request = Request::create('/pay/paysapi/notify_url', 'POST', $payload);

        $response = app(PaysapiController::class)->notifyUrl($request);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('TRADE-SUCCESS', $order->trade_no);
    }

    private function createPaysapiOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Paysapi Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Paysapi Product ' . $orderSn,
            'gd_description' => 'Paysapi Product Description',
            'gd_keywords' => 'paysapi,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Paysapi ' . $orderSn,
            'pay_check' => 'paysapi-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'uid-001',
            'merchant_pem' => 'token-001',
            'pay_handleroute' => '/pay/paysapi',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Paysapi Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => 'account:demo-user',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);
    }
}
