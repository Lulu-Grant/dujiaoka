<?php

namespace Tests\Unit;

use App\Jobs\MailSend;
use App\Models\BaseModel;
use App\Models\Carmis;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Service\OrderFulfillmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderFulfillmentServiceTest extends TestCase
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
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_fulfill_manual_updates_stock_and_dispatches_manage_mail(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Fulfillment Manual Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Fulfillment Manual Product',
            'gd_description' => 'Fulfillment Manual Product Description',
            'gd_keywords' => 'fulfillment,manual',
            'actual_price' => 18.00,
            'in_stock' => 7,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $order = Order::query()->create([
            'order_sn' => 'FULFILLMANUAL001',
            'goods_id' => $goods->id,
            'title' => 'Fulfillment Manual Product x 2',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 18.00,
            'buy_amount' => 2,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 36.00,
            'actual_price' => 36.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => 'account:demo-user',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);

        $service = app(OrderFulfillmentService::class);
        $fulfilledOrder = $service->fulfillManual($order);

        $goods->refresh();
        $order->refresh();

        $this->assertSame(Order::STATUS_PENDING, $fulfilledOrder->status);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(5, $goods->in_stock);
        Queue::assertPushed(MailSend::class);
    }

    public function test_fulfill_automatic_marks_cards_sold_and_completes_order(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Fulfillment Auto Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Fulfillment Auto Product',
            'gd_description' => 'Fulfillment Auto Product Description',
            'gd_keywords' => 'fulfillment,auto',
            'actual_price' => 9.00,
            'in_stock' => 2,
            'sales_volume' => 0,
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $firstCarmi = Carmis::query()->create([
            'goods_id' => $goods->id,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => 'FULFILL-CARD-1',
        ]);

        $secondCarmi = Carmis::query()->create([
            'goods_id' => $goods->id,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => 'FULFILL-CARD-2',
        ]);

        $order = Order::query()->create([
            'order_sn' => 'FULFILLAUTO0001',
            'goods_id' => $goods->id,
            'title' => 'Fulfillment Auto Product x 2',
            'type' => BaseModel::AUTOMATIC_DELIVERY,
            'goods_price' => 9.00,
            'buy_amount' => 2,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 18.00,
            'actual_price' => 18.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => '',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_WAIT_PAY,
        ]);

        $service = app(OrderFulfillmentService::class);
        $fulfilledOrder = $service->fulfillAutomatic($order);

        $order->refresh();
        $firstCarmi->refresh();
        $secondCarmi->refresh();

        $this->assertSame(Order::STATUS_COMPLETED, $fulfilledOrder->status);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertStringContainsString('FULFILL-CARD-1', $order->info);
        $this->assertStringContainsString('FULFILL-CARD-2', $order->info);
        $this->assertSame(Carmis::STATUS_SOLD, $firstCarmi->status);
        $this->assertSame(Carmis::STATUS_SOLD, $secondCarmi->status);
        Queue::assertPushed(MailSend::class);
    }
}
