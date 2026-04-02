<?php

namespace Tests\Unit;

use App\Http\Controllers\Pay\PaypalPayController;
use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class PaypalPayControllerTest extends TestCase
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

    public function test_return_url_redirects_cancelled_payments_back_to_order_detail(): void
    {
        $order = $this->createPaypalOrder('PAYPAL-CANCEL-001');

        $request = Request::create('/pay/paypal/return_url', 'GET', [
            'success' => 'no',
            'orderSN' => $order->order_sn,
            'paymentId' => '',
            'PayerID' => '',
        ]);

        $response = app(PaypalPayController::class)->returnUrl($request);

        $this->assertStringContainsString($order->order_sn, $response->getTargetUrl());
    }

    public function test_return_url_rejects_non_paypal_gateway_orders(): void
    {
        $order = $this->createPaypalOrder('PAYPAL-INVALID-001', '/pay/not-paypal');

        $request = Request::create('/pay/paypal/return_url', 'GET', [
            'success' => 'ok',
            'orderSN' => $order->order_sn,
            'paymentId' => 'PAY-ID',
            'PayerID' => 'PAYER-ID',
        ]);

        $response = app(PaypalPayController::class)->returnUrl($request);

        $this->assertSame('error', $response);
    }

    private function createPaypalOrder(string $orderSn, string $handlerRoute = '/pay/paypal'): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Paypal Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Paypal Product ' . $orderSn,
            'gd_description' => 'Paypal Product Description',
            'gd_keywords' => 'paypal,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Paypal ' . $orderSn,
            'pay_check' => 'paypal-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_key' => 'client-id',
            'merchant_pem' => 'client-secret',
            'pay_handleroute' => $handlerRoute,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Paypal Product x 1',
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
