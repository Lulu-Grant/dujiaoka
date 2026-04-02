<html class="js cssanimations">
<head lang="en">
    <meta charset="UTF-8">
    <title>收银台</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/amazeui@2.7.2/dist/css/amazeui.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@2.1.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.qrcode@1.0.3/jquery.qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/amazeui@2.7.2/dist/js/amazeui.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        @media only screen and (min-width: 641px) {
            .am-offcanvas {
                display: block;
                position: static;
                background: none;
            }

            .am-offcanvas-bar {
                position: static;
                width: auto;
                background: none;
                -webkit-transform: translate3d(0, 0, 0);
                -ms-transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0);
            }

            .am-offcanvas-bar:after {
                content: none;
            }
        }

        @media only screen and (max-width: 640px) {
            .am-offcanvas-bar .am-nav > li > a {
                color: #ccc;
                border-radius: 0;
                border-top: 1px solid rgba(0, 0, 0, .3);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .05)
            }

            .am-offcanvas-bar .am-nav > li > a:hover {
                background: #404040;
                color: #fff
            }

            .am-offcanvas-bar .am-nav > li.am-nav-header {
                color: #777;
                background: #404040;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .05);
                text-shadow: 0 1px 0 rgba(0, 0, 0, .5);
                border-top: 1px solid rgba(0, 0, 0, .3);
                font-weight: 400;
                font-size: 75%
            }

            .am-offcanvas-bar .am-nav > li.am-active > a {
                background: #1a1a1a;
                color: #fff;
                box-shadow: inset 0 1px 3px rgba(0, 0, 0, .3)
            }

            .am-offcanvas-bar .am-nav > li + li {
                margin-top: 0;
            }
        }

        .my-head {
            margin-top: 40px;
            text-align: center;
        }

        .am-tab-panel {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .panel-title {
            display: inline;
            font-weight: bold;
        }

        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }

        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }

        .StripeElement--invalid {
            border-color: #fa755a;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }

        .form-row {
            width: 70%;
            float: left;
        }

        .wrapper {
            width: 670px;
            margin: 0 auto;
        }

        label {
            font-weight: 500;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }

        .button {
            border: none;
            border-radius: 4px;
            outline: none;
            text-decoration: none;
            color: #fff;
            background: #32325d;
            white-space: nowrap;
            display: inline-block;
            height: 40px;
            line-height: 40px;
            padding: 0 14px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.025em;
            -webkit-transition: all 150ms ease;
            transition: all 150ms ease;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<header class="am-g my-head">
    <div class="am-u-sm-12 am-article">
        <h1 class="am-article-title">收银台</h1>
    </div>
</header>
<hr class="am-article-divider">
<div class="am-container">
    <h2>付款信息
        <div class="am-topbar-right">¥{{ $price }}</div>
    </h2>
    <p><small>订单编号：{{ $orderid }}</small></p>
    <div class="am-tabs" data-am-tabs="">
        <ul class="am-tabs-nav am-nav am-nav-tabs">
            <li class="am-active"><a href="#alipay">Alipay 支付宝</a></li>
            <li class="request-wechat-pay"><a href="#wcpay">微信支付</a></li>
            <li class="request-card-pay"><a href="#cardpay">银行卡支付</a></li>
        </ul>
        <div class="am-tabs-bd am-tabs-bd-ofv"
             style="touch-action: pan-y; user-select: none; -webkit-user-drag: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);">
            <div class="am-tab-panel am-active" id="alipay">
                <a class="am-btn am-btn-lg am-btn-warning am-btn-primary" id="alipaybtn" href="#">进入支付宝付款</a>
                <p></p>
            </div>
            <div class="am-tab-panel am-fade" id="wcpay">
                <div class="text-align:center; margin:0 auto; width:60%">
                    <div class="wcpay-qrcode" style="text-align: center;" data-requested="0">
                        正在加载中...
                    </div>
                </div>
            </div>
            <div class="am-tab-panel am-fade" id="cardpay">
                <div class="text-align:center; margin:0 auto; width:60%">
                    <div class="wrapper cardpay_content" style="max-width:500px">
                        <div class="am-alert am-alert-danger" style="display:none">支付失败，请更换卡片或检查输入信息</div>
                        <form action="{{ $charge_url }}" method="post" id="payment-form">
                            <div class="form-row">
                                <label for="card-element">
                                    <p class="am-alert am-alert-secondary">借记卡或信用卡</p>
                                </label>
                                <div id="card-element"></div>
                                <div id="card-errors" role="alert"></div>
                            </div>
                            <div class="form-row">
                                <button class="button">支付</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var stripe = Stripe(@json($publishable_key));
    var source = '';
    var orderId = @json($orderid);
    var amountCny = {{ $amount_cny }};
    var amountUsd = {{ $amount_usd }};
    var returnUrl = @json($return_url);
    var detailUrl = @json($detail_url);
    var checkUrl = @json($check_url);
    var chargeUrl = @json($charge_url);

    var elements = stripe.elements();
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };

    var card = elements.create('card', {style: style});
    card.mount('#card-element');

    card.on('change', function (event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        $(".button").attr("disabled", "true");
        $(".button").html("请稍后");
        stripe.createToken(card).then(function (result) {
            if (result.error) {
                document.getElementById('card-errors').textContent = result.error.message;
            } else {
                stripeTokenHandler(result.token);
            }
        });
    });

    function stripeTokenHandler(token) {
        $.ajax({
            url: chargeUrl + '/?orderid=' + orderId + '&stripeToken=' + token.id,
            type: 'GET',
            success: function (result) {
                if (result === "success") {
                    $(".cardpay_content").html("<p class='am-alert am-alert-success'>支付成功，正在跳转页面</p>");
                    window.setTimeout(function () {
                        location.href = detailUrl;
                    }, 800);
                } else {
                    $(".am-alert").show();
                    $(".button").removeAttr("disabled");
                    $(".button").html("支付");
                    setTimeout(function () {
                        $('.am-alert').hide();
                    }, 3000);
                }
            }
        });
    }

    (function () {
        stripe.createSource({
            type: 'alipay',
            amount: amountCny,
            currency: 'cny',
            owner: {
                name: orderId
            },
            redirect: {
                return_url: returnUrl
            }
        }).then(function (result) {
            $("#alipaybtn").attr("href", result.source.redirect.url);
        });
    })();

    function paymentcheck() {
        $.ajax({
            url: checkUrl + '/?orderid=' + orderId + '&source=' + source,
            type: 'GET',
            success: function (result) {
                if (result === "success") {
                    $(".wcpay-qrcode").html("<p class='am-alert am-alert-success'>支付成功，正在跳转页面</p>");
                    window.setTimeout(function () {
                        location.href = detailUrl;
                    }, 800);
                } else {
                    setTimeout(paymentcheck, 1000);
                }
            }
        });
    }

    $(".request-wechat-pay").click(function () {
        if ($(".wcpay-qrcode").data("requested") == 0) {
            stripe.createSource({
                type: 'wechat',
                amount: amountUsd,
                currency: 'usd',
                owner: {
                    name: orderId
                }
            }).then(function (result) {
                if (result.source && result.source.id) {
                    $(".wcpay-qrcode").html("<p class='am-alert am-alert-success'>打开微信 - 扫一扫</p>");
                    $(".wcpay-qrcode").qrcode(result.source.wechat.qr_code_url);
                    $(".wcpay-qrcode").data("requested", 1);
                    source = result.source.id;
                    setTimeout(paymentcheck, 3000);
                } else {
                    $(".wcpay-qrcode").html("<p class='am-alert am-alert-danger'>加载失败，请刷新页面。</p>");
                }
            });
        }
    });
</script>
</body>
</html>
