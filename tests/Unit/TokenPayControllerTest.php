<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\TokenPayController;
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

class TokenPayControllerTest extends TestCase
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
        $order = $this->createTokenPayOrder('TOKENPAY-FAIL-001');

        $request = Request::create('/pay/tokenpay/notify_url', 'POST', [
            'OutOrderId' => $order->order_sn,
            'ActualAmount' => '10',
            'Id' => 'TRADE-FAIL',
            'Signature' => 'invalid-signature',
        ]);

        $response = app(TokenPayController::class)->notifyUrl($request);

        $this->assertSame('fail', $response);
    }

    public function test_notify_url_completes_order_with_valid_signature(): void
    {
        $order = $this->createTokenPayOrder('TOKENPAY-SUCCESS-001');
        $payGateway = Pay::query()->findOrFail($order->pay_id);

        $payload = [
            'OutOrderId' => $order->order_sn,
            'ActualAmount' => '10',
            'Id' => 'TRADE-SUCCESS',
        ];
        $payload['Signature'] = $this->tokenPaySignature($payload, $payGateway->merchant_key);

        $request = Request::create('/pay/tokenpay/notify_url', 'POST', $payload);

        $response = app(TokenPayController::class)->notifyUrl($request);

        $order->refresh();

        $this->assertSame('ok', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('TRADE-SUCCESS', $order->trade_no);
    }

    private function tokenPaySignature(array $parameter, string $signKey): string
    {
        ksort($parameter);
        reset($parameter);
        $sign = '';
        foreach ($parameter as $key => $val) {
            if ($key === 'Signature') {
                continue;
            }

            if ($sign !== '') {
                $sign .= '&';
            }

            $sign .= "$key=$val";
        }

        return md5($sign . $signKey);
    }

    private function createTokenPayOrder(string $orderSn): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'TokenPay Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'TokenPay Product ' . $orderSn,
            'gd_description' => 'TokenPay Product Description',
            'gd_keywords' => 'tokenpay,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'TokenPay ' . $orderSn,
            'pay_check' => 'tokenpay-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'USDT',
            'merchant_key' => 'tokenpay-secret',
            'merchant_pem' => 'https://gateway.example.com',
            'pay_handleroute' => '/pay/tokenpay',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'TokenPay Product x 1',
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
