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

    public function test_complete_payment_is_idempotent_for_duplicate_manual_processing_notification(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Duplicate Payment Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Duplicate Payment Product',
            'gd_description' => 'Duplicate Payment Product Description',
            'gd_keywords' => 'payment,duplicate',
            'actual_price' => 11.00,
            'in_stock' => 5,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $order = Order::query()->create([
            'order_sn' => 'PAYMENTSERVICE002',
            'goods_id' => $goods->id,
            'title' => 'Duplicate Payment Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
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
        $service->completePayment($order->order_sn, 11.00, 'TRADE-PAY-002');
        $duplicateOrder = $service->completePayment($order->order_sn, 11.00, 'TRADE-PAY-002');

        $goods->refresh();
        $order->refresh();

        $this->assertSame(Order::STATUS_PENDING, $duplicateOrder->status);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('TRADE-PAY-002', $order->trade_no);
        $this->assertSame(1, $goods->sales_volume);
    }

    public function test_complete_payment_rejects_inconsistent_amount_before_side_effects(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Mismatch Payment Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Mismatch Payment Product',
            'gd_description' => 'Mismatch Payment Product Description',
            'gd_keywords' => 'payment,mismatch',
            'actual_price' => 11.00,
            'in_stock' => 5,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $order = Order::query()->create([
            'order_sn' => 'PAYMENTSERVICE003',
            'goods_id' => $goods->id,
            'title' => 'Mismatch Payment Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('dujiaoka.prompt.order_inconsistent_amounts'));

        try {
            app(OrderPaymentService::class)->completePayment($order->order_sn, 10.00, 'TRADE-PAY-003');
        } finally {
            $goods->refresh();
            $order->refresh();

            $this->assertSame(Order::STATUS_WAIT_PAY, $order->status);
            $this->assertSame('', (string) $order->trade_no);
            $this->assertSame(0, $goods->sales_volume);
        }
    }
}
