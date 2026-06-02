@extends('avatar.layouts.default')
@section('content')
@php
    $payName = $pay['pay_name'] ?? '未配置';
    $payHandle = $pay['pay_handleroute'] ?? null;
    $payCheck = $pay['pay_check'] ?? null;
@endphp
<section class="avatar-bill-shell">
    <div class="avatar-panel avatar-bill-panel">
        <div class="avatar-bill-header">
            <h1 class="avatar-page-title">{{ __('hyper.bill_title') }}</h1>
            <p class="avatar-page-copy">请确认订单信息，确认无误后继续支付。</p>
        </div>

        <div class="avatar-bill-list">
            <div class="avatar-bill-row"><span>{{ __('hyper.bill_order_number') }}</span><strong>{{ $order_sn }}</strong></div>
            <div class="avatar-bill-row"><span>{{ __('hyper.bill_product_name') }}</span><strong>{{ $title }}</strong></div>
            <div class="avatar-bill-row"><span>{{ __('hyper.bill_commodity_price') }}</span><strong>{{ $goods_price }}</strong></div>
            <div class="avatar-bill-row"><span>{{ __('hyper.bill_purchase_quantity') }}</span><strong>x {{ $buy_amount }}</strong></div>
            @if(!empty($coupon))
                <div class="avatar-bill-row"><span>{{ __('hyper.bill_promo_code') }}</span><strong>{{ $coupon['coupon'] }}</strong></div>
                <div class="avatar-bill-row"><span>{{ __('hyper.bill_discounted_price') }}</span><strong>{{ $coupon_discount_price }}</strong></div>
            @endif
            <div class="avatar-bill-row avatar-bill-total"><span>{{ __('hyper.bill_actual_payment') }}</span><strong>{{ $actual_price }}</strong></div>
            <div class="avatar-bill-row"><span>{{ __('hyper.bill_email') }}</span><strong>{{ $email }}</strong></div>
            @if(!empty($info))
                <div class="avatar-bill-row"><span>{{ __('hyper.bill_order_information') }}</span><strong>{{ $info }}</strong></div>
            @endif
            <div class="avatar-bill-row"><span>{{ __('hyper.bill_payment_method') }}</span><strong>{{ $payName }}</strong></div>
        </div>

        @if($payHandle && $payCheck)
            <a href="{{ url('pay-gateway', ['handle' => urlencode($payHandle),'payway' => $payCheck, 'orderSN' => $order_sn]) }}"
               class="avatar-button avatar-button--primary avatar-submit">
                {{ __('hyper.bill_pay_immediately') }}
            </a>
        @else
            <button type="button" class="avatar-button avatar-button--secondary avatar-submit" disabled>
                支付方式未配置
            </button>
        @endif
    </div>
</section>
@stop
@section('js')
@stop
