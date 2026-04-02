<?php

use App\Models\Emailtpl;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the bootstrap email template seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->templates() as $template) {
            Emailtpl::withTrashed()->updateOrCreate(
                ['tpl_token' => $template['tpl_token']],
                [
                    'tpl_name' => $template['tpl_name'],
                    'tpl_content' => $template['tpl_content'],
                    'deleted_at' => null,
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function templates(): array
    {
        return [
            [
                'tpl_token' => 'card_send_user_email',
                'tpl_name' => '【{webname}】感谢您的购买，请查收您的收据',
                'tpl_content' => <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{webname}</title>
</head>
<body style="margin:0;background:#f4f6fb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#1f2937;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 12px;">
    <tr>
        <td align="center">
            <table width="680" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:32px 36px;background:#102542;color:#ffffff;">
                        <div style="font-size:13px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.8;">{webname}</div>
                        <h1 style="margin:12px 0 0;font-size:28px;line-height:1.3;">{ord_title}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 36px;">
                        <p style="margin:0 0 16px;">感谢您的购买，以下是本次订单信息。</p>
                        <p style="margin:0 0 8px;">订单号：{order_id}</p>
                        <p style="margin:0 0 8px;">下单时间：{created_at}</p>
                        <p style="margin:0 0 24px;">商品：{product_name} × {buy_amount}</p>
                        <div style="padding:18px;border-radius:12px;background:#f8fafc;border:1px solid #e5e7eb;white-space:pre-wrap;">{ord_info}</div>
                        <p style="margin:24px 0 0;font-weight:600;">订单金额：{ord_price} ¥</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 36px 32px;">
                        <a href="{weburl}" style="display:inline-block;padding:12px 18px;background:#102542;color:#ffffff;text-decoration:none;border-radius:999px;">访问 {webname}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
HTML,
            ],
            [
                'tpl_token' => 'manual_send_manage_mail',
                'tpl_name' => '【{webname}】新订单等待处理！',
                'tpl_content' => <<<'HTML'
<p>尊敬的管理员：</p>
<p>客户购买的商品 <strong>【{product_name}】</strong> 已支付成功，请及时处理。</p>
<p>订单号：{order_id}</p>
<p>数量：{buy_amount}</p>
<p>金额：{ord_price}</p>
<p>时间：{created_at}</p>
<hr>
<p>{ord_info}</p>
<hr>
<p><strong>来自 {webname} - {weburl}</strong></p>
HTML,
            ],
            [
                'tpl_token' => 'failed_order',
                'tpl_name' => '【{webname}】订单处理失败！',
                'tpl_content' => <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{webname}</title>
</head>
<body style="margin:0;background:#fff7ed;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#7c2d12;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 12px;">
    <tr>
        <td align="center">
            <table width="680" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border:1px solid #fed7aa;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:32px 36px;background:#9a3412;color:#ffffff;">
                        <div style="font-size:13px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.8;">订单处理异常</div>
                        <h1 style="margin:12px 0 0;font-size:28px;line-height:1.3;">{ord_title}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 36px;">
                        <p style="margin:0 0 16px;">非常遗憾，您的订单处理失败，请及时联系站点管理员。</p>
                        <p style="margin:0 0 8px;">订单号：{order_id}</p>
                        <p style="margin:0 0 8px;">下单时间：{created_at}</p>
                        <p style="margin:0 0 24px;">商品：{product_name} × {buy_amount}</p>
                        <div style="padding:18px;border-radius:12px;background:#fff7ed;border:1px solid #fdba74;white-space:pre-wrap;">{ord_info}</div>
                        <p style="margin:24px 0 0;font-weight:600;">订单金额：{ord_price} ¥</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
HTML,
            ],
            [
                'tpl_token' => 'completed_order',
                'tpl_name' => '【{webname}】您的订单已处理完成',
                'tpl_content' => <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{webname}</title>
</head>
<body style="margin:0;background:#ecfdf5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#14532d;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 12px;">
    <tr>
        <td align="center">
            <table width="680" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border:1px solid #86efac;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:32px 36px;background:#166534;color:#ffffff;">
                        <div style="font-size:13px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.8;">订单已完成</div>
                        <h1 style="margin:12px 0 0;font-size:28px;line-height:1.3;">{ord_title}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 36px;">
                        <p style="margin:0 0 16px;">您的订单已处理完成，请登录站点核对处理结果。</p>
                        <p style="margin:0 0 8px;">订单号：{order_id}</p>
                        <p style="margin:0 0 8px;">处理时间：{created_at}</p>
                        <div style="padding:18px;border-radius:12px;background:#f0fdf4;border:1px solid #bbf7d0;white-space:pre-wrap;">{ord_info}</div>
                        <p style="margin:24px 0 0;font-weight:600;">订单金额：{ord_price} ¥</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
HTML,
            ],
            [
                'tpl_token' => 'pending_order',
                'tpl_name' => '【{webname}】已收到您的订单，请等候处理',
                'tpl_content' => <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{webname}</title>
</head>
<body style="margin:0;background:#eff6ff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#1e3a8a;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 12px;">
    <tr>
        <td align="center">
            <table width="680" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border:1px solid #bfdbfe;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:32px 36px;background:#1d4ed8;color:#ffffff;">
                        <div style="font-size:13px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.8;">订单已接收</div>
                        <h1 style="margin:12px 0 0;font-size:28px;line-height:1.3;">{ord_title}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 36px;">
                        <p style="margin:0 0 16px;">已收到您的订单，系统已通知工作人员处理，请耐心等待后续结果。</p>
                        <p style="margin:0 0 8px;">订单号：{order_id}</p>
                        <p style="margin:0 0 24px;">下单时间：{created_at}</p>
                        <p style="margin:0;font-weight:600;">订单金额：{ord_price} ¥</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
HTML,
            ],
        ];
    }
}
