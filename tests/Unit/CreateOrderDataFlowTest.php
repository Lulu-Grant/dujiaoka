<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Pay;
use App\Service\DataTransferObjects\CreateOrderData;
use App\Service\OrderProcessService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateOrderDataFlowTest extends TestCase
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

    public function test_create_order_from_data_builds_pending_order_without_setters(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'DTO Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'DTO Product',
            'gd_description' => 'DTO Product Description',
            'gd_keywords' => 'dto,product',
            'actual_price' => 13.50,
            'in_stock' => 20,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'DTO Pay',
            'pay_check' => 'dto-test-pay',
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
            'account:dto-user',
            2,
            $pay->id,
            'buyer@example.com',
            '127.0.0.1',
            'search-pass'
        ));

        $order->refresh();

        $this->assertSame($goods->id, $order->goods_id);
        $this->assertSame($pay->id, $order->pay_id);
        $this->assertSame('DTO Product x 2', $order->title);
        $this->assertSame(OrderProcessService::PENDING_CACHE_KEY, OrderProcessService::PENDING_CACHE_KEY);
        $this->assertSame('27.00', $order->total_price);
        $this->assertSame('27.00', $order->actual_price);
        $this->assertSame('account:dto-user', $order->info);
        $this->assertSame('search-pass', $order->search_pwd);
    }
}
