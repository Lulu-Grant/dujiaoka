<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\WepayNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WepayNotificationServiceTest extends TestCase
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

    public function test_handle_notification_returns_error_when_order_is_missing(): void
    {
        $service = new class extends WepayNotificationService {
            protected function buildPayClient(string $appId, string $mchId, string $key)
            {
                return new \stdClass();
            }
        };

        $this->assertSame('error', $service->handleNotification('MISSING-WEPAY-ORDER'));
    }

    public function test_handle_notification_completes_order_after_successful_verification(): void
    {
        $order = $this->createWepayOrder('WEPAY-SUCCESS-001');

        $service = new class extends WepayNotificationService {
            protected function buildPayClient(string $appId, string $mchId, string $key)
            {
                return new \stdClass();
            }

            protected function verifyNotification($pay)
            {
                return (object) [
                    'out_trade_no' => 'WEPAY-SUCCESS-001',
                    'total_fee' => '1000',
                    'transaction_id' => 'WEPAY-TRADE-001',
                ];
            }
        };

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('WEPAY-TRADE-001', $order->trade_no);
    }

    public function test_handle_notification_rejects_verification_exception_without_side_effects(): void
    {
        $order = $this->createWepayOrder('WEPAY-SIGNATURE-001');
        $goods = $order->goods;

        $service = new class extends WepayNotificationService {
            protected function buildPayClient(string $appId, string $mchId, string $key)
            {
                return new \stdClass();
            }

            protected function verifyNotification($pay)
            {
                throw new \Exception('invalid signature');
            }
        };

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();
        $goods->refresh();

        $this->assertSame('fail', $response);
        $this->assertSame(Order::STATUS_WAIT_PAY, $order->status);
        $this->assertSame('', (string) $order->trade_no);
        $this->assertSame(0, $goods->sales_volume);
    }

    public function test_handle_notification_rejects_amount_mismatch_without_side_effects(): void
    {
        $order = $this->createWepayOrder('WEPAY-AMOUNT-001');
        $goods = $order->goods;
        $service = $this->wepayServiceForResult('WEPAY-AMOUNT-001', '1200', 'WEPAY-TRADE-AMOUNT');

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();
        $goods->refresh();

        $this->assertSame('fail', $response);
        $this->assertSame(Order::STATUS_WAIT_PAY, $order->status);
        $this->assertSame('', (string) $order->trade_no);
        $this->assertSame(0, $goods->sales_volume);
    }

    public function test_handle_notification_is_idempotent_for_duplicate_notification(): void
    {
        $order = $this->createWepayOrder('WEPAY-DUPLICATE-001');
        $goods = $order->goods;
        $service = $this->wepayServiceForResult('WEPAY-DUPLICATE-001', '1000', 'WEPAY-TRADE-DUPLICATE');

        $firstResponse = $service->handleNotification($order->order_sn);
        $secondResponse = $service->handleNotification($order->order_sn);

        $order->refresh();
        $goods->refresh();

        $this->assertSame('success', $firstResponse);
        $this->assertSame('success', $secondResponse);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('WEPAY-TRADE-DUPLICATE', $order->trade_no);
        $this->assertSame(1, $goods->sales_volume);
    }

    public function test_handle_notification_rejects_completed_order_with_different_trade_number(): void
    {
        $order = $this->createWepayOrder('WEPAY-COMPLETED-001', Order::STATUS_COMPLETED, 'WEPAY-TRADE-ORIGINAL', 3);
        $goods = $order->goods;
        $service = $this->wepayServiceForResult('WEPAY-COMPLETED-001', '1000', 'WEPAY-TRADE-DIFFERENT');

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();
        $goods->refresh();

        $this->assertSame('fail', $response);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame('WEPAY-TRADE-ORIGINAL', $order->trade_no);
        $this->assertSame(3, $goods->sales_volume);
    }

    private function wepayServiceForResult(string $orderSn, string $totalFee, string $tradeNo): WepayNotificationService
    {
        return new class($orderSn, $totalFee, $tradeNo) extends WepayNotificationService {
            private $orderSn;
            private $totalFee;
            private $tradeNo;

            public function __construct(string $orderSn, string $totalFee, string $tradeNo)
            {
                $this->orderSn = $orderSn;
                $this->totalFee = $totalFee;
                $this->tradeNo = $tradeNo;
                parent::__construct();
            }

            protected function buildPayClient(string $appId, string $mchId, string $key)
            {
                return new \stdClass();
            }

            protected function verifyNotification($pay)
            {
                return (object) [
                    'out_trade_no' => $this->orderSn,
                    'total_fee' => $this->totalFee,
                    'transaction_id' => $this->tradeNo,
                ];
            }
        };
    }

    private function createWepayOrder(
        string $orderSn,
        int $status = Order::STATUS_WAIT_PAY,
        string $tradeNo = '',
        int $salesVolume = 0
    ): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Wepay Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Wepay Product ' . $orderSn,
            'gd_description' => 'Wepay Product Description',
            'gd_keywords' => 'wepay,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => $salesVolume,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Wepay ' . $orderSn,
            'pay_check' => 'wepay-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'wechat-app-id',
            'merchant_key' => 'wechat-mch-id',
            'merchant_pem' => 'wechat-key',
            'pay_handleroute' => '/pay/wepay',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Wepay Product x 1',
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
