<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\YipayController;
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

class YipayControllerTest extends TestCase
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
        $order = $this->createYipayOrder('YIPAY-FAIL-001');

        $request = Request::create('/pay/yipay/notify_url', 'GET', [
            'out_trade_no' => $order->order_sn,
            'trade_no' => 'TRADE-FAIL',
            'money' => '10',
            'sign_type' => 'MD5',
            'sign' => 'invalid-signature',
        ]);

        $response = app(YipayController::class)->notifyUrl($request);

        $this->assertSame('fail', $response);
    }

    public function test_notify_url_completes_order_with_valid_signature(): void
    {
        $order = $this->createYipayOrder('YIPAY-SUCCESS-001');
        $payGateway = Pay::query()->findOrFail($order->pay_id);

        $payload = [
            'out_trade_no' => $order->order_sn,
            'trade_no' => 'TRADE-SUCCESS',
            'money' => '10',
            'sign_type' => 'MD5',
        ];

        $signSource = $payload;
        ksort($signSource);
        reset($signSource);
        $sign = '';
        foreach ($signSource as $key => $val) {
            if ($key == 'sign' || $key == 'sign_type' || $val == '') {
                continue;
            }
            if ($sign != '') {
                $sign .= '&';
            }
            $sign .= "$key=$val";
        }
        $payload['sign'] = md5($sign . $payGateway->merchant_pem);

        $request = Request::create('/pay/yipay/notify_url', 'GET', $payload);

        $response = app(YipayController::class)->notifyUrl($request);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('TRADE-SUCCESS', $order->trade_no);
    }

    public function test_notify_url_rejects_amount_mismatch_without_side_effects(): void
    {
        $order = $this->createYipayOrder('YIPAY-AMOUNT-001');
        $goods = $order->goods;

        $response = app(YipayController::class)->notifyUrl(
            Request::create('/pay/yipay/notify_url', 'GET', $this->signedPayload($order, 'TRADE-AMOUNT', '12'))
        );

        $order->refresh();
        $goods->refresh();

        $this->assertSame('fail', $response);
        $this->assertSame(Order::STATUS_WAIT_PAY, $order->status);
        $this->assertSame('', (string) $order->trade_no);
        $this->assertSame(0, $goods->sales_volume);
    }

    public function test_notify_url_is_idempotent_for_duplicate_notification(): void
    {
        $order = $this->createYipayOrder('YIPAY-DUPLICATE-001');
        $goods = $order->goods;
        $payload = $this->signedPayload($order, 'TRADE-DUPLICATE', '10');

        $firstResponse = app(YipayController::class)->notifyUrl(
            Request::create('/pay/yipay/notify_url', 'GET', $payload)
        );
        $secondResponse = app(YipayController::class)->notifyUrl(
            Request::create('/pay/yipay/notify_url', 'GET', $payload)
        );

        $order->refresh();
        $goods->refresh();

        $this->assertSame('success', $firstResponse);
        $this->assertSame('success', $secondResponse);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('TRADE-DUPLICATE', $order->trade_no);
        $this->assertSame(1, $goods->sales_volume);
    }

    public function test_notify_url_rejects_completed_order_with_different_trade_number(): void
    {
        $order = $this->createYipayOrder('YIPAY-COMPLETED-001', Order::STATUS_COMPLETED, 'TRADE-ORIGINAL', 3);
        $goods = $order->goods;

        $response = app(YipayController::class)->notifyUrl(
            Request::create('/pay/yipay/notify_url', 'GET', $this->signedPayload($order, 'TRADE-DIFFERENT', '10'))
        );

        $order->refresh();
        $goods->refresh();

        $this->assertSame('fail', $response);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame('TRADE-ORIGINAL', $order->trade_no);
        $this->assertSame(3, $goods->sales_volume);
    }

    /**
     * @return array<string, string>
     */
    private function signedPayload(Order $order, string $tradeNo, string $money): array
    {
        $payGateway = Pay::query()->findOrFail($order->pay_id);

        $payload = [
            'out_trade_no' => $order->order_sn,
            'trade_no' => $tradeNo,
            'money' => $money,
            'sign_type' => 'MD5',
        ];

        $signSource = $payload;
        ksort($signSource);
        reset($signSource);
        $sign = '';
        foreach ($signSource as $key => $val) {
            if ($key == 'sign' || $key == 'sign_type' || $val == '') {
                continue;
            }
            if ($sign != '') {
                $sign .= '&';
            }
            $sign .= "$key=$val";
        }
        $payload['sign'] = md5($sign . $payGateway->merchant_pem);

        return $payload;
    }

    private function createYipayOrder(
        string $orderSn,
        int $status = Order::STATUS_WAIT_PAY,
        string $tradeNo = '',
        int $salesVolume = 0
    ): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Yipay Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Yipay Product ' . $orderSn,
            'gd_description' => 'Yipay Product Description',
            'gd_keywords' => 'yipay,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => $salesVolume,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Yipay ' . $orderSn,
            'pay_check' => 'yipay-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_key' => 'https://gateway.example.com/pay',
            'merchant_pem' => 'yipay-secret',
            'pay_handleroute' => '/pay/yipay',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Yipay Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 10.00,
            'buy_amount' => 1,
            'total_price' => 10.00,
            'actual_price' => 10.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => 'account:demo-user',
            'buy_ip' => '127.0.0.1',
            'status' => $status,
            'trade_no' => $tradeNo,
        ]);
    }
}
