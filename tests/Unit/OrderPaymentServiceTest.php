<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Carmis;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Service\OrderPaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderPaymentServiceTest extends TestCase
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
        ]);
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_complete_payment_updates_trade_number_and_sales_volume(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Payment Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Payment Product',
            'gd_description' => 'Payment Product Description',
            'gd_keywords' => 'payment,product',
            'actual_price' => 11.00,
            'in_stock' => 2,
            'sales_volume' => 0,
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        Carmis::query()->create([
            'goods_id' => $goods->id,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => 'PAY-CARD-001',
        ]);

        $order = Order::query()->create([
            'order_sn' => 'PAYMENTSERVICE001',
            'goods_id' => $goods->id,
            'title' => 'Payment Product x 1',
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'goods_price' => 11.00,
            'buy_amount' => 1,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 11.00,
            'actual_price' => 11.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => '',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);

        $service = app(OrderPaymentService::class);
        $completedOrder = $service->completePayment($order->order_sn, 11.00, 'TRADE-PAY-001');

        $goods->refresh();
        $order->refresh();

        $this->assertSame(Order::STATUS_COMPLETED, $completedOrder->status);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame('TRADE-PAY-001', $order->trade_no);
        $this->assertSame(1, $goods->sales_volume);
    }
}
