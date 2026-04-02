<?php

namespace Tests\Unit;

use App\Jobs\ApiHook;
use App\Jobs\MailSend;
use App\Models\BaseModel;
use App\Models\Carmis;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\DataTransferObjects\CreateOrderData;
use App\Service\OrderProcessService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderProcessServiceTest extends TestCase
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
            'is_open_server_jiang' => BaseModel::STATUS_CLOSE,
            'is_open_telegram_push' => BaseModel::STATUS_CLOSE,
            'is_open_bark_push' => BaseModel::STATUS_CLOSE,
            'is_open_qywxbot_push' => BaseModel::STATUS_CLOSE,
            'text_logo' => 'Test Shop',
            'manage_email' => 'admin@example.com',
        ]);
    }

    protected function tearDown(): void
    {
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_completed_order_for_automatic_delivery_marks_cards_sold(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Auto Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Auto Product',
            'gd_description' => 'Auto Product Description',
            'gd_keywords' => 'auto,product',
            'actual_price' => 10.00,
            'in_stock' => 2,
            'sales_volume' => 0,
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $firstCarmi = Carmis::query()->create([
            'goods_id' => $goods->id,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => 'CARD-001',
        ]);

        $secondCarmi = Carmis::query()->create([
            'goods_id' => $goods->id,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => 'CARD-002',
        ]);

        $order = Order::query()->create([
            'order_sn' => 'AUTOORDER000001',
            'goods_id' => $goods->id,
            'title' => 'Auto Product x 2',
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'goods_price' => 10.00,
            'buy_amount' => 2,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 20.00,
            'actual_price' => 20.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => '',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);

        $service = app(OrderProcessService::class);
        $completedOrder = $service->completedOrder($order->order_sn, 20.00, 'TRADE-001');

        $goods->refresh();
        $firstCarmi->refresh();
        $secondCarmi->refresh();
        $order->refresh();

        $this->assertSame(Order::STATUS_COMPLETED, $completedOrder->status);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame('TRADE-001', $order->trade_no);
        $this->assertStringContainsString('CARD-001', $order->info);
        $this->assertStringContainsString('CARD-002', $order->info);
        $this->assertSame(Carmis::STATUS_SOLD, $firstCarmi->status);
        $this->assertSame(Carmis::STATUS_SOLD, $secondCarmi->status);
        $this->assertSame(2, $goods->sales_volume);

        Queue::assertPushed(MailSend::class);
        Queue::assertPushed(ApiHook::class);
    }

    public function test_create_order_persists_expected_pricing_without_delayed_queue_dependency(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Manual Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Manual Product',
            'gd_description' => 'Manual Product Description',
            'gd_keywords' => 'manual,product',
            'actual_price' => 19.90,
            'in_stock' => 99,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Test Pay',
            'pay_check' => 'service-test-pay',
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_pem' => 'secret',
            'pay_handleroute' => '/pay/test',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $service = app(OrderProcessService::class);
        $order = $service->createOrderFromData(new CreateOrderData(
            $goods,
            null,
            'account:demo-user',
            2,
            $pay->id,
            'buyer@example.com',
            '127.0.0.1',
            ''
        ));

        $order->refresh();

        $this->assertSame($goods->id, $order->goods_id);
        $this->assertSame($pay->id, $order->pay_id);
        $this->assertSame(Order::STATUS_WAIT_PAY, $order->status);
        $this->assertSame('Manual Product x 2', $order->title);
        $this->assertSame('39.80', $order->total_price);
        $this->assertSame('39.80', $order->actual_price);
        $this->assertSame('account:demo-user', $order->info);
        $this->assertSame('buyer@example.com', $order->email);

        Queue::assertNothingPushed();
    }

    public function test_completed_order_marks_order_abnormal_when_cards_are_insufficient(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Short Stock Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Short Stock Product',
            'gd_description' => 'Short Stock Product Description',
            'gd_keywords' => 'short,stock',
            'actual_price' => 15.00,
            'in_stock' => 2,
            'sales_volume' => 0,
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        Carmis::query()->create([
            'goods_id' => $goods->id,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => 'ONLY-CARD',
        ]);

        $order = Order::query()->create([
            'order_sn' => 'AUTOORDER000002',
            'goods_id' => $goods->id,
            'title' => 'Short Stock Product x 2',
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'goods_price' => 15.00,
            'buy_amount' => 2,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 30.00,
            'actual_price' => 30.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => '',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);

        $service = app(OrderProcessService::class);
        $completedOrder = $service->completedOrder($order->order_sn, 30.00, 'TRADE-002');

        $goods->refresh();
        $order->refresh();

        $this->assertSame(Order::STATUS_ABNORMAL, $completedOrder->status);
        $this->assertSame(Order::STATUS_ABNORMAL, $order->status);
        $this->assertStringContainsString('卡密', $order->info);
        $this->assertSame(2, $goods->sales_volume);

        Queue::assertPushed(ApiHook::class);
        Queue::assertNotPushed(MailSend::class);
    }

    public function test_create_order_with_coupon_updates_discount_and_coupon_usage(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Coupon Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Coupon Product',
            'gd_description' => 'Coupon Product Description',
            'gd_keywords' => 'coupon,product',
            'actual_price' => 20.00,
            'in_stock' => 10,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Coupon Pay',
            'pay_check' => 'coupon-test-pay',
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_pem' => 'secret',
            'pay_handleroute' => '/pay/test',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $coupon = Coupon::query()->create([
            'discount' => 5.00,
            'is_use' => Coupon::STATUS_UNUSED,
            'is_open' => BaseModel::STATUS_OPEN,
            'coupon' => 'SAVE5',
            'ret' => 2,
        ]);

        \DB::table('coupons_goods')->insert([
            'goods_id' => $goods->id,
            'coupons_id' => $coupon->id,
        ]);

        $service = app(OrderProcessService::class);
        $order = $service->createOrderFromData(new CreateOrderData(
            $goods,
            $coupon,
            null,
            2,
            $pay->id,
            'buyer@example.com',
            '127.0.0.1',
            ''
        ));

        $coupon->refresh();
        $order->refresh();

        $this->assertSame('40.00', $order->total_price);
        $this->assertSame('5.00', $order->coupon_discount_price);
        $this->assertSame('35.00', $order->actual_price);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(Coupon::STATUS_USE, $coupon->is_use);
        $this->assertSame(1, $coupon->ret);
    }

    public function test_completed_order_for_manual_processing_reduces_stock_and_dispatches_mail_jobs(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Manual Complete Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Manual Complete Product',
            'gd_description' => 'Manual Complete Description',
            'gd_keywords' => 'manual,complete',
            'actual_price' => 30.00,
            'in_stock' => 8,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $order = Order::query()->create([
            'order_sn' => 'MANUALORDER0001',
            'goods_id' => $goods->id,
            'title' => 'Manual Complete Product x 2',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 30.00,
            'buy_amount' => 2,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 60.00,
            'actual_price' => 60.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => 'account:demo-user',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);

        $service = app(OrderProcessService::class);
        $completedOrder = $service->completedOrder($order->order_sn, 60.00, 'TRADE-003');

        $goods->refresh();
        $order->refresh();

        $this->assertSame(Order::STATUS_PENDING, $completedOrder->status);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('TRADE-003', $order->trade_no);
        $this->assertSame(6, $goods->in_stock);
        $this->assertSame(2, $goods->sales_volume);

        Queue::assertPushed(MailSend::class, 2);
        Queue::assertPushed(ApiHook::class);
    }

    public function test_create_order_applies_wholesale_discount_to_actual_price(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Wholesale Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Wholesale Product',
            'gd_description' => 'Wholesale Product Description',
            'gd_keywords' => 'wholesale,product',
            'actual_price' => 10.00,
            'wholesale_price_cnf' => '3=8.50',
            'in_stock' => 99,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Wholesale Pay',
            'pay_check' => 'wholesale-test-pay',
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_pem' => 'secret',
            'pay_handleroute' => '/pay/test',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $service = app(OrderProcessService::class);
        $order = $service->createOrderFromData(new CreateOrderData(
            $goods,
            null,
            null,
            3,
            $pay->id,
            'buyer@example.com',
            '127.0.0.1',
            ''
        ));

        $this->assertEquals(30.00, (float) $order->total_price);
        $this->assertEquals(4.50, (float) $order->wholesale_discount_price);
        $this->assertEquals(25.50, (float) $order->actual_price);
        $this->assertSame('Wholesale Product x 3', $order->title);
    }
}
