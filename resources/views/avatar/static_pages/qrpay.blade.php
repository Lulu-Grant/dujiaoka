@extends('avatar.layouts.default')
@section('content')
<section class="avatar-pay-shell">
    <div class="avatar-panel avatar-pay-panel">
        <div class="avatar-pay-header">
            <h1 class="avatar-page-title">{{ __('hyper.qrpay_title') }}</h1>
            <p class="avatar-page-copy">{{ __('hyper.qrpay_order_expiration_date') }} {{ dujiaoka_config_get('order_expire_time', 5) }} {{ __('hyper.qrpay_expiration_date') }}</p>
        </div>
        <div class="avatar-qrcode-wrap">
            <div id="pay-qrcode" class="avatar-qrcode" data-qr-code="{{ e($qr_code) }}"></div>
            <noscript>
                <p class="avatar-page-copy">{{ $qr_code }}</p>
            </noscript>
        </div>
        <div class="avatar-pay-total">
            <span>{{ __('hyper.qrpay_actual_payment') }}</span>
            <strong>{{ $actual_price }}</strong>
        </div>
        @if(Agent::isMobile() && isset($jump_payuri))
            <a href="{{ $jump_payuri }}" class="avatar-button avatar-button--primary">{{ __('hyper.qrpay_open_app_to_pay') }}</a>
        @endif
    </div>
</section>
@stop
@section('js')
    <script src="/vendor/dcat-admin/dcat/plugins/jquery-qrcode/dist/jquery-qrcode.min.js"></script>
    <script>
        $(function () {
            var qrText = $('#pay-qrcode').data('qr-code');
            if (qrText) {
                $('#pay-qrcode').empty().qrcode({
                    width: 200,
                    height: 200,
                    text: qrText
                });
            }
        });

        var getting = {
            url:'{{ url('check-order-status', ['orderSN' => $orderid]) }}',
            dataType:'json',
            success:function(res) {
                if (res.code == 400001) {
                    window.clearTimeout(timer);
                    $.NotificationApp.send("{{ __('hyper.qrpay_notice') }}","{{ __('hyper.order_pay_timeout') }}","top-center","rgba(0,0,0,0.2)","warning");
                    setTimeout("window.location.href ='/'",3000);
                }
                if (res.code == 200) {
                    window.clearTimeout(timer);
                    $.NotificationApp.send("{{ __('hyper.qrpay_notice') }}","{{ __('hyper.payment_successful') }}","top-center","rgba(0,0,0,0.2)","success");
                    setTimeout("window.location.href ='{{ url('detail-order-sn', ['orderSN' => $orderid]) }}'",3000);
                }
            }
        };
        var timer = window.setInterval(function(){$.ajax(getting)},5000);
    </script>
@stop
