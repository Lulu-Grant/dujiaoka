<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Stripe 收银台</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/assets/avatar/css/stripe-checkout.css">
    <script src="/assets/avatar/js/jquery-3.6.0.min.js"></script>
    <script src="/vendor/dcat-admin/dcat/plugins/jquery-qrcode/dist/jquery-qrcode.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="stripe-checkout-page">
<div class="stripe-checkout-shell">
    <header class="stripe-checkout-hero">
        <span class="stripe-checkout-eyebrow">Stripe Checkout</span>
        <h1 class="stripe-checkout-title">独角数卡西瓜版收银台</h1>
        <div class="stripe-checkout-summary">
            <span>订单编号：<strong>{{ $orderid }}</strong></span>
            <span>应付金额：<strong>¥{{ $price }}</strong></span>
        </div>
    </header>

    <section class="stripe-checkout-panel">
        <div class="stripe-checkout-tabs">
            <button class="stripe-checkout-tab is-active" type="button" data-target="#stripe-alipay">支付宝</button>
            <button class="stripe-checkout-tab request-wechat-pay" type="button" data-target="#stripe-wechat">微信支付</button>
            <button class="stripe-checkout-tab" type="button" data-target="#stripe-card">银行卡</button>
        </div>

        <div class="stripe-checkout-content">
            <section class="stripe-checkout-pane is-active" id="stripe-alipay">
                <div class="stripe-checkout-card">
                    <p class="stripe-checkout-copy">通过 Stripe 创建支付宝支付链接，完成后会自动跳回订单详情页。</p>
                    <a class="stripe-checkout-button" id="alipaybtn" href="#">进入支付宝付款</a>
                </div>
            </section>

            <section class="stripe-checkout-pane" id="stripe-wechat">
                <div class="stripe-checkout-card">
                    <div class="stripe-checkout-alert" id="wechat-alert"></div>
                    <div class="stripe-checkout-qrcode" id="wechat-qrcode" data-requested="0">正在等待生成微信二维码...</div>
                </div>
            </section>

            <section class="stripe-checkout-pane" id="stripe-card">
                <div class="stripe-checkout-card">
                    <div class="stripe-checkout-alert" id="card-alert"></div>
                    <form action="{{ $charge_url }}" method="post" id="payment-form">
                        <div class="stripe-checkout-field-label">借记卡或信用卡</div>
                        <div class="stripe-checkout-card-box">
                            <div id="card-element"></div>
                        </div>
                        <div class="stripe-checkout-errors" id="card-errors" role="alert"></div>
                        <button class="stripe-checkout-button" id="card-submit" type="submit">支付</button>
                    </form>
                </div>
            </section>
        </div>
    </section>
</div>
@php($stripeCheckoutConfig = [
    'publishableKey' => $publishable_key,
    'orderId' => $orderid,
    'amountCny' => $amount_cny,
    'amountTarget' => $amount_usd,
    'sourceCurrency' => $source_currency,
    'targetCurrency' => $target_currency,
    'returnUrl' => $return_url,
    'detailUrl' => $detail_url,
    'checkUrl' => $check_url,
    'chargeUrl' => $charge_url,
])
<script>
    window.stripeCheckoutConfig = {!! json_encode($stripeCheckoutConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
</script>
<script src="/assets/avatar/js/stripe-checkout.js"></script>
</body>
</html>
