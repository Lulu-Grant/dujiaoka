@extends('avatar.layouts.seo')
@section('content')
<section class="avatar-buy-grid">
    <div class="avatar-buy-main">
        <div class="avatar-panel avatar-product-summary">
            @if(!empty($picture))
                <div class="avatar-product-media">
                    <img src="{{ picture_ulr($picture) }}" alt="{{ $gd_name }}" onerror="this.onerror=null;this.src='/assets/avatar/images/ui/product-digital-card.webp';">
                </div>
            @else
                <div class="avatar-product-media">
                    <img src="/assets/avatar/images/ui/product-digital-card.webp" alt="{{ $gd_name }}">
                </div>
            @endif
            <h1 class="avatar-product-title">{{ $gd_name }}</h1>
            <div class="avatar-product-meta">
                @if($type == \App\Models\Goods::AUTOMATIC_DELIVERY)
                    <span class="avatar-badge">{{ __('hyper.buy_automatic_delivery') }}</span>
                @else
                    <span class="avatar-badge">{{ __('hyper.buy_charge') }}</span>
                @endif
                <span class="avatar-badge">{{ __('hyper.buy_in_stock') }} {{ $in_stock }}</span>
                @if($buy_limit_num > 0)
                    <span class="avatar-badge">{{ __('hyper.buy_purchase_restrictions') }} {{ $buy_limit_num }}</span>
                @endif
            </div>
            <div class="avatar-price-row">
                <span class="avatar-price">{{ __('hyper.global_currency') }} {{ $actual_price }}</span>
                <del class="avatar-old-price">¥ {{ $retail_price }}</del>
            </div>
            @if(!empty($gd_description))
                <p class="avatar-page-copy" style="margin-top: 14px;">{{ $gd_description }}</p>
            @endif
        </div>

        <div class="avatar-panel avatar-prose">
            <h2>{{ __('hyper.buy_product_desciption') }}</h2>
            {!! $description !!}
        </div>
    </div>

    <aside class="avatar-panel avatar-checkout-panel">
        <div class="avatar-checkout-assurance">
            <span>安全交付</span>
            <span>自动发货</span>
        </div>
        <h2 class="avatar-checkout-title">提交订单</h2>
        <form id="buy-form" action="{{ url('create-order') }}" method="post" class="avatar-form">
            {{ csrf_field() }}
            <input type="hidden" name="gid" value="{{ $id }}">

            @if(!empty($wholesale_price_cnf) && is_array($wholesale_price_cnf))
                <div class="avatar-panel" style="padding: 12px; background: var(--avatar-surface-soft);">
                    @foreach($wholesale_price_cnf as $ws)
                        <div class="avatar-page-copy">{{ __('hyper.buy_purchase') }} {{ $ws['number'] }} {{__('hyper.buy_the_above')}}，{{ $ws['price'] }} {{__('hyper.buy_each')}}。</div>
                    @endforeach
                </div>
            @endif

            <label class="avatar-field">
                <span class="avatar-label">{{ __('hyper.buy_email') }}</span>
                <input type="email" name="email" class="form-control" placeholder="{{ __('hyper.buy_input_account') }}">
            </label>

            <label class="avatar-field">
                <span class="avatar-label">{{ __('hyper.buy_purchase_quantity') }}</span>
                <div class="avatar-quantity">
                    <button type="button" class="avatar-quantity-minus" aria-label="减少数量">-</button>
                    <input type="text" name="by_amount" value="1" inputmode="numeric" data-bts-max="999">
                    <button type="button" class="avatar-quantity-plus" aria-label="增加数量">+</button>
                </div>
            </label>

            @if(dujiaoka_config_get('is_open_search_pwd') == \App\Models\Goods::STATUS_OPEN)
                <label class="avatar-field">
                    <span class="avatar-label">{{ __('hyper.buy_search_password') }}</span>
                    <input type="text" name="search_pwd" value="" class="form-control" placeholder="{{ __('hyper.buy_input_search_password') }}">
                </label>
            @endif

            @if(isset($open_coupon))
                <label class="avatar-field">
                    <span class="avatar-label">{{ __('hyper.buy_promo_code') }}</span>
                    <input type="text" name="coupon_code" class="form-control" placeholder="{{ __('hyper.buy_input_promo_code') }}">
                </label>
            @endif

            @if($type == \App\Models\Goods::MANUAL_PROCESSING && is_array($other_ipu))
                @foreach($other_ipu as $ipu)
                    <label class="avatar-field">
                        <span class="avatar-label">{{ $ipu['desc'] }}</span>
                        <input type="text" name="{{ $ipu['field'] }}" @if($ipu['rule'] !== false) required @endif class="form-control" placeholder="{{ $ipu['placeholder'] }}">
                    </label>
                @endforeach
            @endif

            @if(dujiaoka_config_get('is_open_img_code') == \App\Models\Goods::STATUS_OPEN)
                <label class="avatar-field">
                    <span class="avatar-label">{{ __('hyper.buy_verify_code') }}</span>
                    <div class="input-group">
                        <input type="text" name="img_verify_code" value="" class="form-control" placeholder="{{ __('hyper.buy_verify_code') }}">
                        <div class="input-group-append">
                            <div class="buy-captcha">
                                <img class="captcha-img" src="{{ captcha_src('buy') . time() }}" onclick="refresh()" style="cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </label>
                <script>
                    function refresh(){
                        $('img[class="captcha-img"]').attr('src','{{ captcha_src('buy') }}'+Math.random());
                    }
                </script>
            @endif

            <div class="avatar-field">
                <span class="avatar-label">{{ __('hyper.buy_payment_method') }}</span>
                <input type="hidden" name="payway" value="{{ $payways[0]['id'] ?? 0 }}">
                <div class="avatar-payment-grid">
                    @foreach($payways as $key => $way)
                        @php
                            $payCheck = strtolower((string)($way['pay_check'] ?? ''));
                            $payName = (string)($way['pay_name'] ?? '');
                            $payIcon = null;
                            $payTone = 'default';
                            if (str_contains($payCheck, 'epusdt') || str_contains(strtolower($payName), 'usdt')) {
                                $payIcon = '/assets/avatar/images/ui/icon-usdt.svg';
                                $payDisplayName = 'USDT';
                                $payTone = 'usdt';
                            } elseif (str_contains($payCheck, 'ali') || str_contains($payCheck, 'zfb') || str_contains($payName, '支付宝')) {
                                $payIcon = '/assets/avatar/images/ui/icon-alipay.svg';
                                $payDisplayName = '支付宝';
                                $payTone = 'alipay';
                            } elseif (str_contains($payCheck, 'wx') || str_contains($payCheck, 'we') || str_contains($payName, '微信')) {
                                $payIcon = '/assets/avatar/images/ui/icon-wechat.svg';
                                $payDisplayName = '微信支付';
                                $payTone = 'wechat';
                            } else {
                                $payDisplayName = $payName;
                            }
                        @endphp
                        <button type="button"
                                class="avatar-payment-option avatar-payment-option--{{ $payTone }} pay-type @if($key == 0) active @endif"
                                data-type="{{ $way['pay_check'] }}"
                                data-id="{{ $way['id'] }}"
                                data-name="{{ $way['pay_name'] }}">
                            @if($payIcon)
                                <img src="{{ $payIcon }}" alt="" aria-hidden="true">
                            @endif
                            <span>{{ $payDisplayName }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="avatar-button avatar-button--primary avatar-submit" id="submit">
                {{ __('hyper.buy_order_now') }}
            </button>
        </form>
    </aside>
</section>

<div class="modal fade" id="buy_prompt" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myCenterModalLabel">{{ __('hyper.buy_purchase_tips') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                {!! $buy_prompt !!}
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="img-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: none;">
        <img id="img-zoom" style="border-radius: 5px;">
    </div>
</div>
@stop
@section('js')
<script>
    $('.avatar-quantity-minus').click(function(){
        var input = $("input[name='by_amount']");
        var current = parseInt(input.val(), 10) || 1;
        input.val(Math.max(1, current - 1));
    });
    $('.avatar-quantity-plus').click(function(){
        var input = $("input[name='by_amount']");
        var current = parseInt(input.val(), 10) || 1;
        var max = parseInt(input.data('bts-max'), 10) || 999;
        input.val(Math.min(max, current + 1));
    });
    $('#submit').click(function(){
        if($("input[name='email']").val() == ''){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.buy_empty_mailbox') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        if($("input[name='by_amount']").val() == 0 ){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.buy_zero_quantity') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        if($("input[name='by_amount']").val() > {{ $in_stock }}){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.buy_exceeds_stock') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        @if($buy_limit_num > 0)
        if($("input[name='by_amount']").val() > {{ $buy_limit_num }}){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.buy_exceeds_limit') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        @endif
        @if(dujiaoka_config_get('is_open_search_pwd') == \App\Models\Goods::STATUS_OPEN)
        if($("input[name='search_pwd']").val() == 0){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.buy_empty_query_password') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        @endif
        @if(dujiaoka_config_get('is_open_img_code') == \App\Models\Goods::STATUS_OPEN)
        if($("input[name='img_verify_code']").val() == ''){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.buy_empty_captcha') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        @endif
    });
</script>
<script>
    @if(!empty($buy_prompt))
        window.AvatarUI.showModal('#buy_prompt');
    @endif
    $(function() {
        $("#img-zoom").click(function(){
            window.AvatarUI.hideModal('#img-modal');
        });
        $("#img-dialog").click(function(){
            window.AvatarUI.hideModal('#img-modal');
        });
        $(".avatar-prose img").each(function(i){
            var src = $(this).attr("src");
            $(this).click(function () {
                $("#img-zoom").attr("src", src);
                var oImg = $(this);
                var img = new Image();
                img.src = $(oImg).attr("src");
                var realWidth = img.width;
                var realHeight = img.height;
                var ww = $(window).width();
                var hh = $(window).height();
                $("#img-content").css({"top":0,"left":0,"height":"auto"});
                $("#img-zoom").css({"height":"auto"});
                $("#img-zoom").css({"margin-left":"auto"});
                $("#img-zoom").css({"margin-right":"auto"});
                if((realWidth+20)>ww){
                    $("#img-content").css({"width":"100%"});
                    $("#img-zoom").css({"width":"100%"});
                }else{
                    $("#img-content").css({"width":realWidth+20, "height":realHeight+20});
                    $("#img-zoom").css({"width":realWidth, "height":realHeight});
                }
                if((hh-realHeight-40)>0){
                    $("#img-content").css({"top":(hh-realHeight-40)/2});
                }
                if((ww-realWidth-20)>0){
                    $("#img-content").css({"left":(ww-realWidth-20)/2});
                }
                window.AvatarUI.showModal('#img-modal');
            });
        });
    });
</script>
@stop
