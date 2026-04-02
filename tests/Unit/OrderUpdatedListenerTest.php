<?php

namespace Tests\Unit;

use App\Events\OrderUpdated as OrderUpdatedEvent;
use App\Jobs\MailSend;
use App\Listeners\OrderUpdated;
use App\Models\BaseModel;
use App\Models\Emailtpl;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderUpdatedListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
        config(['dujiaoka.async_side_effects' => true]);
        Queue::fake();
        Cache::put('system-setting', [
            'text_logo' => 'Test Shop',
        ]);
    }

    protected function tearDown(): void
    {
        config(['dujiaoka.async_side_effects' => false]);
        Model::reguard();

        parent::tearDown();
    }

    public function test_listener_dispatches_pending_status_mail_for_manual_orders(): void
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Listener Group',
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Listener Product',
            'gd_description' => 'Listener Product Description',
            'gd_keywords' => 'listener,product',
            'actual_price' => 12.00,
            'in_stock' => 50,
            'sales_volume' => 0,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        Emailtpl::query()->updateOrCreate(
            ['tpl_token' => 'pending_order'],
            [
                'tpl_name' => 'Pending {order_id}',
                'tpl_content' => 'Info:{ord_info}',
            ]
        );

        $order = Order::query()->create([
            'order_sn' => 'LISTENER000001',
            'goods_id' => $goods->id,
            'title' => 'Listener Product x 1',
            'type' => BaseModel::MANUAL_PROCESSING,
            'goods_price' => 12.00,
            'buy_amount' => 1,
            'coupon_discount_price' => 0,
            'wholesale_discount_price' => 0,
            'total_price' => 12.00,
            'actual_price' => 12.00,
            'search_pwd' => '',
            'email' => 'buyer@example.com',
            'info' => 'account:demo-user',
            'buy_ip' => '127.0.0.1',
            'status' => Order::STATUS_PENDING,
        ]);

        app(OrderUpdated::class)->handle(new OrderUpdatedEvent($order));

        Queue::assertPushed(MailSend::class, function (MailSend $job) {
            return $this->readJobProperty($job, 'to') === 'buyer@example.com'
                && $this->readJobProperty($job, 'title') === 'Pending LISTENER000001'
                && str_contains($this->readJobProperty($job, 'content'), 'account:demo-user');
        });
    }

    private function readJobProperty(MailSend $job, string $property)
    {
        $reflection = new \ReflectionProperty($job, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($job);
    }
}
