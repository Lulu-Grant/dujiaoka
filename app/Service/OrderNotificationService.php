<?php

namespace App\Service;

use App\Jobs\ApiHook;
use App\Jobs\BarkPush;
use App\Jobs\MailSend;
use App\Jobs\ServerJiang;
use App\Jobs\TelegramPush;
use App\Jobs\WorkWeiXinPush;
use App\Models\BaseModel;
use App\Models\Order;

class OrderNotificationService
{
    /**
     * @var \App\Service\EmailtplService
     */
    private $emailtplService;

    /**
     * @var \App\Service\SideEffectDispatcherService
     */
    private $sideEffectDispatcher;

    public function __construct()
    {
        $this->emailtplService = app('Service\EmailtplService');
        $this->sideEffectDispatcher = app(SideEffectDispatcherService::class);
    }

    /**
     * 派发订单完成后的通知与回调
     *
     * @param Order $order
     */
    public function dispatchOrderSideEffects(Order $order): void
    {
        if (dujiaoka_config_get('is_open_server_jiang', 0) == BaseModel::STATUS_OPEN) {
            $this->sideEffectDispatcher->dispatch(new ServerJiang($order));
        }
        if (dujiaoka_config_get('is_open_telegram_push', 0) == BaseModel::STATUS_OPEN) {
            $this->sideEffectDispatcher->dispatch(new TelegramPush($order));
        }
        if (dujiaoka_config_get('is_open_bark_push', 0) == BaseModel::STATUS_OPEN) {
            $this->sideEffectDispatcher->dispatch(new BarkPush($order));
        }
        if (dujiaoka_config_get('is_open_qywxbot_push', 0) == BaseModel::STATUS_OPEN) {
            $this->sideEffectDispatcher->dispatch(new WorkWeiXinPush($order));
        }

        $this->sideEffectDispatcher->dispatch(new ApiHook($order));
    }

    /**
     * 向管理员发送人工处理通知
     *
     * @param Order $order
     */
    public function sendManualManageMail(Order $order): void
    {
        $this->sendTemplateMail(
            dujiaoka_config_get('manage_email', ''),
            'manual_send_manage_mail',
            $this->buildOrderMailData($order, str_replace(PHP_EOL, '<br/>', $order->info))
        );
    }

    /**
     * 向用户发送自动发货邮件
     *
     * @param Order $order
     * @param string $orderInfo
     */
    public function sendAutomaticDeliveryMail(Order $order, string $orderInfo): void
    {
        $this->sendTemplateMail(
            $order->email,
            'card_send_user_email',
            $this->buildOrderMailData($order, $orderInfo)
        );
    }

    /**
     * 按订单状态发送邮件
     *
     * @param Order $order
     */
    public function sendOrderStatusMail(Order $order): void
    {
        if ($order->type != Order::MANUAL_PROCESSING) {
            return;
        }

        $token = $this->resolveOrderStatusMailToken($order);
        if (!$token) {
            return;
        }

        $this->sendTemplateMail(
            $order->email,
            $token,
            $this->buildOrderMailData($order, str_replace(PHP_EOL, '<br/>', $order->info))
        );
    }

    /**
     * 构造订单邮件模板数据
     *
     * @param Order $order
     * @param string $orderInfo
     * @return array<string, mixed>
     */
    public function buildOrderMailData(Order $order, string $orderInfo): array
    {
        return [
            'created_at' => $order->created_at ?: date('Y-m-d H:i'),
            'product_name' => $order->goods->gd_name,
            'webname' => dujiaoka_config_get('text_logo', '独角数卡西瓜版'),
            'weburl' => config('app.url') ?? 'http://dujiaoka.com',
            'ord_info' => $orderInfo,
            'ord_title' => $order->title,
            'order_id' => $order->order_sn,
            'buy_amount' => $order->buy_amount,
            'ord_price' => $order->actual_price,
        ];
    }

    /**
     * 派发邮件模板
     *
     * @param string $to
     * @param string $token
     * @param array<string, mixed> $mailData
     */
    public function sendTemplateMail(string $to, string $token, array $mailData): void
    {
        $tpl = $this->emailtplService->detailByToken($token);
        if (!$tpl) {
            return;
        }

        $mailBody = replace_mail_tpl($tpl, $mailData);
        $this->sideEffectDispatcher->dispatch(new MailSend($to, $mailBody['tpl_name'], $mailBody['tpl_content']));
    }

    /**
     * 根据订单状态解析模板 token
     *
     * @param Order $order
     * @return string|null
     */
    private function resolveOrderStatusMailToken(Order $order): ?string
    {
        switch ($order->status) {
            case Order::STATUS_PENDING:
                return 'pending_order';
            case Order::STATUS_COMPLETED:
                return 'completed_order';
            case Order::STATUS_FAILURE:
                return 'failed_order';
            default:
                return null;
        }
    }
}
