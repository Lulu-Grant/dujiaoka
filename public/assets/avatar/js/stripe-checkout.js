(function (window, document, $) {
    'use strict';

    function showAlert(selector, message, success) {
        var $alert = $(selector);
        $alert
            .removeClass('is-danger is-success')
            .addClass(success ? 'is-success' : 'is-danger')
            .addClass('is-visible')
            .text(message);
    }

    function hideAlert(selector) {
        $(selector).removeClass('is-visible').text('');
    }

    function activateTab(target) {
        $('.stripe-checkout-tab').removeClass('is-active');
        $('.stripe-checkout-pane').removeClass('is-active');
        $('.stripe-checkout-tab[data-target="' + target + '"]').addClass('is-active');
        $(target).addClass('is-active');
    }

    $(function () {
        var config = window.stripeCheckoutConfig || {};
        var stripe = window.Stripe(config.publishableKey);
        var sourceId = '';
        var elements = stripe.elements();
        var card = elements.create('card', {
            style: {
                base: {
                    color: '#0f172a',
                    fontFamily: '"Helvetica Neue", Helvetica, Arial, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#94a3b8'
                    }
                },
                invalid: {
                    color: '#dc2626',
                    iconColor: '#dc2626'
                }
            }
        });

        card.mount('#card-element');

        $('.stripe-checkout-tab').on('click', function () {
            activateTab($(this).data('target'));
        });

        card.on('change', function (event) {
            $('#card-errors').text(event.error ? event.error.message : '');
        });

        $('#payment-form').on('submit', function (event) {
            event.preventDefault();
            hideAlert('#card-alert');
            $('#card-submit').attr('disabled', true).text('请稍后');

            stripe.createToken(card).then(function (result) {
                if (result.error) {
                    $('#card-errors').text(result.error.message);
                    $('#card-submit').attr('disabled', false).text('支付');
                    return;
                }

                $.ajax({
                    url: config.chargeUrl + '/?orderid=' + config.orderId + '&stripeToken=' + result.token.id,
                    type: 'GET',
                    success: function (response) {
                        if (response === 'success') {
                            showAlert('#card-alert', '支付成功，正在跳转页面。', true);
                            window.setTimeout(function () {
                                window.location.href = config.detailUrl;
                            }, 800);
                            return;
                        }

                        showAlert('#card-alert', '支付失败，请更换卡片或检查输入信息。', false);
                        $('#card-submit').attr('disabled', false).text('支付');
                    }
                });
            });
        });

        function pollPayment() {
            $.ajax({
                url: config.checkUrl + '/?orderid=' + config.orderId + '&source=' + sourceId,
                type: 'GET',
                success: function (response) {
                    if (response === 'success') {
                        showAlert('#wechat-alert', '支付成功，正在跳转页面。', true);
                        window.setTimeout(function () {
                            window.location.href = config.detailUrl;
                        }, 800);
                        return;
                    }

                    window.setTimeout(pollPayment, 1000);
                }
            });
        }

        stripe.createSource({
            type: 'alipay',
            amount: config.amountCny,
            currency: config.sourceCurrency,
            owner: {
                name: config.orderId
            },
            redirect: {
                return_url: config.returnUrl
            }
        }).then(function (result) {
            if (result.source && result.source.redirect && result.source.redirect.url) {
                $('#alipaybtn').attr('href', result.source.redirect.url);
            }
        });

        $('.request-wechat-pay').on('click', function () {
            if ($('#wechat-qrcode').data('requested') === 1) {
                return;
            }

            stripe.createSource({
                type: 'wechat',
                amount: config.amountTarget,
                currency: config.targetCurrency,
                owner: {
                    name: config.orderId
                }
            }).then(function (result) {
                if (result.source && result.source.id) {
                    $('#wechat-alert').removeClass('is-danger').addClass('is-success is-visible').text('打开微信扫一扫完成支付');
                    $('#wechat-qrcode').empty().qrcode(result.source.wechat.qr_code_url).data('requested', 1);
                    sourceId = result.source.id;
                    window.setTimeout(pollPayment, 3000);
                    return;
                }

                showAlert('#wechat-alert', '加载失败，请刷新页面后重试。', false);
            });
        });
    });
})(window, document, window.jQuery);
