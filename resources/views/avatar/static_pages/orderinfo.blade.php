@extends('avatar.layouts.default')
@section('content')
<section class="avatar-orderinfo-shell">
    <div class="avatar-page-heading">
        <h1 class="avatar-page-title">{{ __('hyper.orderinfo_title') }}</h1>
    </div>

    <div class="avatar-orderinfo-grid">
        @foreach($orders as $order)
            <article class="avatar-panel avatar-orderinfo-card">
                <div class="avatar-orderinfo-header">
                    <span class="avatar-badge">订单号：{{ $order['order_sn'] }}</span>
                </div>
                <div class="avatar-orderinfo-card-grid">
                    <div class="avatar-orderinfo-list">
                        <div class="avatar-orderinfo-row"><span>{{ __('hyper.orderinfo_order_title') }}</span><strong>{{ $order['title'] }}</strong></div>
                        <div class="avatar-orderinfo-row"><span>{{ __('hyper.orderinfo_number_of_orders') }}</span><strong>{{ $order['buy_amount'] }}</strong></div>
                        <div class="avatar-orderinfo-row"><span>{{ __('hyper.orderinfo_order_time') }}</span><strong>{{ $order['created_at'] }}</strong></div>
                        <div class="avatar-orderinfo-row"><span>{{ __('hyper.orderinfo_email') }}</span><strong>{{ $order['email'] }}</strong></div>
                        <div class="avatar-orderinfo-row">
                            <span>{{ __('hyper.orderinfo_order_class') }}</span>
                            <strong>
                                @if($order['type'] == \App\Models\Order::AUTOMATIC_DELIVERY)
                                    {{ __('hyper.orderinfo_automatic_delivery') }}
                                @else
                                    {{ __('hyper.orderinfo_charge') }}
                                @endif
                            </strong>
                        </div>
                        <div class="avatar-orderinfo-row"><span>{{ __('hyper.orderinfo_total_order_price') }}</span><strong>{{ $order['actual_price'] }}</strong></div>
                        <div class="avatar-orderinfo-row">
                            <span>{{ __('hyper.orderinfo_order_status') }}</span>
                            <strong>
                                @switch($order['status'])
                                    @case(\App\Models\Order::STATUS_EXPIRED)
                                        {{ __('hyper.orderinfo_status_expired') }}
                                    @break
                                    @case(\App\Models\Order::STATUS_WAIT_PAY)
                                        {{ __('hyper.orderinfo_status_wait_pay') }}
                                    @break
                                    @case(\App\Models\Order::STATUS_PENDING)
                                        {{ __('hyper.orderinfo_status_pending') }}
                                    @break
                                    @case(\App\Models\Order::STATUS_PROCESSING)
                                        {{ __('hyper.orderinfo_status_processed') }}
                                    @break
                                    @case(\App\Models\Order::STATUS_COMPLETED)
                                        {{ __('hyper.orderinfo_status_completed') }}
                                    @break
                                    @case(\App\Models\Order::STATUS_FAILURE)
                                        {{ __('hyper.orderinfo_status_failed') }}
                                    @break
                                    @case(\App\Models\Order::STATUS_FAILURE)
                                        {{ __('hyper.orderinfo_status_abnormal') }}
                                    @break
                                @endswitch
                            </strong>
                        </div>
                        <div class="avatar-orderinfo-row"><span>{{ __('hyper.orderinfo_payment_method') }}</span><strong>{{ $order['pay']['pay_name'] ?? '' }}</strong></div>
                    </div>
                    <div class="avatar-orderinfo-kami">
                        <h2>{{ __('hyper.orderinfo_carmi') }}</h2>
                        <textarea class="avatar-input avatar-kami-textarea textarea-kami" rows="5">{{$order['info']}}</textarea>
                        <button class="avatar-button avatar-button--secondary kami-btn" data-clipboard-text="{{$order['info']}}">
                            {{ __('hyper.orderinfo_copy_carmi') }}
                        </button>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if(!count($orders))
        <div class="avatar-empty-state avatar-panel">
            <div class="avatar-empty-code">error</div>
            <h2>{{ __('hyper.orderinfo_order_information') }}</h2>
            <a class="avatar-button avatar-button--secondary" href="javascript:history.back(-1);"><span class="avatar-inline-icon" aria-hidden="true">&lt;</span> {{ __('hyper.error_back_btn') }}</a>
        </div>
    @endif
</section>
@stop

@section('js')
<script src="/assets/avatar/js/clipboard.min.js"></script>
<script>
    var clipboard = new ClipboardJS('.kami-btn');
    clipboard.on('success', function(e){
        $.NotificationApp.send("{{ __('hyper.orderinfo_tips') }}","{{ __('hyper.orderinfo_copy_success') }}","top-center","rgba(0,0,0,0.2)","info");
    });
    clipboard.on('error', function(e){
        $.NotificationApp.send("{{ __('hyper.orderinfo_tips') }}","{{ __('hyper.orderinfo_copy_error') }}","top-center","rgba(0,0,0,0.2)","error");
    });
</script>
@stop
