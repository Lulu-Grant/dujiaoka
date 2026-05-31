<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\Goods;
use App\Models\GoodsGroup;
use App\Models\Order;
use App\Models\Pay;
use App\Service\AlipayNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AlipayNotificationServiceTest extends TestCase
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
        $service = new class extends AlipayNotificationService {
            protected function buildPayClient(string $appId, string $publicKey, string $privateKey)
            {
                return new \stdClass();
            }
        };

        $this->assertSame('error', $service->handleNotification('MISSING-ALIPAY-ORDER'));
    }

    public function test_handle_notification_completes_order_after_successful_verification(): void
    {
        $order = $this->createAlipayOrder('ALIPAY-SUCCESS-001');

        $service = new class extends AlipayNotificationService {
            protected function buildPayClient(string $appId, string $publicKey, string $privateKey)
            {
                return new \stdClass();
            }

            protected function verifyNotification($pay)
            {
                return (object) [
                    'trade_status' => 'TRADE_SUCCESS',
                    'out_trade_no' => 'ALIPAY-SUCCESS-001',
                    'total_amount' => '10.00',
                    'trade_no' => 'ALIPAY-TRADE-001',
                ];
            }
        };

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();

        $this->assertSame('success', $response);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('ALIPAY-TRADE-001', $order->trade_no);
    }

    public function test_handle_notification_rejects_verification_exception_without_side_effects(): void
    {
        $order = $this->createAlipayOrder('ALIPAY-SIGNATURE-001');
        $goods = $order->goods;

        $service = new class extends AlipayNotificationService {
            protected function buildPayClient(string $appId, string $publicKey, string $privateKey)
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
        $order = $this->createAlipayOrder('ALIPAY-AMOUNT-001');
        $goods = $order->goods;
        $service = $this->alipayServiceForResult('ALIPAY-AMOUNT-001', '12.00', 'ALIPAY-TRADE-AMOUNT');

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
        $order = $this->createAlipayOrder('ALIPAY-DUPLICATE-001');
        $goods = $order->goods;
        $service = $this->alipayServiceForResult('ALIPAY-DUPLICATE-001', '10.00', 'ALIPAY-TRADE-DUPLICATE');

        $firstResponse = $service->handleNotification($order->order_sn);
        $secondResponse = $service->handleNotification($order->order_sn);

        $order->refresh();
        $goods->refresh();

        $this->assertSame('success', $firstResponse);
        $this->assertSame('success', $secondResponse);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('ALIPAY-TRADE-DUPLICATE', $order->trade_no);
        $this->assertSame(1, $goods->sales_volume);
    }

    public function test_handle_notification_rejects_completed_order_with_different_trade_number(): void
    {
        $order = $this->createAlipayOrder('ALIPAY-COMPLETED-001', Order::STATUS_COMPLETED, 'ALIPAY-TRADE-ORIGINAL', 3);
        $goods = $order->goods;
        $service = $this->alipayServiceForResult('ALIPAY-COMPLETED-001', '10.00', 'ALIPAY-TRADE-DIFFERENT');

        $response = $service->handleNotification($order->order_sn);

        $order->refresh();
        $goods->refresh();

        $this->assertSame('fail', $response);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame('ALIPAY-TRADE-ORIGINAL', $order->trade_no);
        $this->assertSame(3, $goods->sales_volume);
    }

    private function alipayServiceForResult(string $orderSn, string $amount, string $tradeNo): AlipayNotificationService
    {
        return new class($orderSn, $amount, $tradeNo) extends AlipayNotificationService {
            private $orderSn;
            private $amount;
            private $tradeNo;

            public function __construct(string $orderSn, string $amount, string $tradeNo)
            {
                $this->orderSn = $orderSn;
                $this->amount = $amount;
                $this->tradeNo = $tradeNo;
                parent::__construct();
            }

            protected function buildPayClient(string $appId, string $publicKey, string $privateKey)
            {
                return new \stdClass();
            }

            protected function verifyNotification($pay)
            {
                return (object) [
                    'trade_status' => 'TRADE_SUCCESS',
                    'out_trade_no' => $this->orderSn,
                    'total_amount' => $this->amount,
                    'trade_no' => $this->tradeNo,
                ];
            }
        };
    }

    private function createAlipayOrder(
        string $orderSn,
        int $status = Order::STATUS_WAIT_PAY,
        string $tradeNo = '',
        int $salesVolume = 0
    ): Order
    {
        $group = GoodsGroup::query()->create([
            'gp_name' => 'Alipay Group ' . $orderSn,
            'is_open' => BaseModel::STATUS_OPEN,
            'ord' => 1,
        ]);

        $goods = Goods::query()->create([
            'group_id' => $group->id,
            'gd_name' => 'Alipay Product ' . $orderSn,
            'gd_description' => 'Alipay Product Description',
            'gd_keywords' => 'alipay,product',
            'actual_price' => 10.00,
            'in_stock' => 10,
            'sales_volume' => $salesVolume,
            'type' => BaseModel::MANUAL_PROCESSING,
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        $pay = Pay::query()->create([
            'pay_name' => 'Alipay ' . $orderSn,
            'pay_check' => 'alipay-' . strtolower($orderSn),
            'pay_method' => Pay::METHOD_JUMP,
            'pay_client' => Pay::PAY_CLIENT_PC,
            'merchant_id' => 'alipay-app-id',
            'merchant_key' => 'alipay-public-key',
            'merchant_pem' => 'alipay-private-key',
            'pay_handleroute' => '/pay/alipay',
            'is_open' => BaseModel::STATUS_OPEN,
        ]);

        return Order::query()->create([
            'order_sn' => $orderSn,
            'goods_id' => $goods->id,
            'pay_id' => $pay->id,
            'title' => 'Alipay Product x 1',
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
