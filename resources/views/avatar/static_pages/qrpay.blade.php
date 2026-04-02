@extends('avatar.layouts.default')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="page-title-box">
            {{-- 扫码支付 --}}
            <h4 class="page-title">{{ __('hyper.qrpay_title') }}</h4>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-primary border">
            <div class="card-body">
                <h5 class="card-title text-primary text-center">{{ __('hyper.qrpay_order_expiration_date') }} {{ dujiaoka_config_get('order_expire_time', 5) }} {{ __('hyper.qrpay_expiration_date') }}</h5>
                <div class="text-center">
                    <div id="pay-qrcode" class="d-inline-block" data-qr-code="{{ e($qr_code) }}"></div>
                    <noscript>
                        <p class="mt-3">{{ $qr_code }}</p>
                    </noscript>
                </div>
                {{-- 订单金额 --}}
                <p class="card-text text-center">{{ __('hyper.qrpay_actual_payment') }}: {{ $actual_price }}</p>
                @if(Agent::isMobile() && isset($jump_payuri))
                    <p class="errpanl" style="text-align: center"><a href="{{ $jump_payuri }}" class="">{{ __('hyper.qrpay_open_app_to_pay') }}</a></p>
                @endif
            </div> <!-- end card-body-->
        </div>
    </div>
</div>
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
