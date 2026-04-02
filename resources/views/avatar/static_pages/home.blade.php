@extends('avatar.layouts.default')
@section('content')
<section class="avatar-hero">
    <div class="avatar-hero-copy">
        <span class="avatar-eyebrow">独角数卡西瓜版</span>
        <h1>{{ dujiaoka_config_get('title') }}</h1>
        <p>{{ dujiaoka_config_get('description') ?: '更清晰的商品浏览，更顺滑的下单入口，以及更现代的前台视觉。' }}</p>
        <div class="avatar-hero-actions">
            <button type="button" class="avatar-primary-btn" id="notice-open">{{ __('hyper.notice_announcement') }}</button>
            <a class="avatar-secondary-btn" href="{{ url('order-search') }}">查询订单</a>
        </div>
    </div>
    <div class="avatar-hero-art">
        <img src="/assets/avatar/images/index-img.webp" alt="独角数卡西瓜版首页视觉">
    </div>
</section>
<section class="avatar-toolbar">
    <div class="avatar-search">
        <input type="text" class="form-control" id="search" placeholder="{{ __('hyper.home_search_box') }}">
    </div>
    <div class="avatar-category-tabs nav nav-list">
        <a href="#group-all" class="tab-link active" data-bs-toggle="tab" aria-expanded="false" role="tab" data-toggle="tab">
        <span class="tab-title">
            {{ __('hyper.home_whole') }}
        </span>
        <div class="img-checkmark">
            <img src="/assets/avatar/images/check.png">
        </div>
        </a>
        @foreach($data as  $index => $group)
            <a href="#group-{{ $group['id'] }}" class="tab-link" data-bs-toggle="tab" aria-expanded="false" role="tab" data-toggle="tab">
                <span class="tab-title">{{ $group['gp_name'] }}</span>
                <div class="img-checkmark">
                    <img src="/assets/avatar/images/check.png">
                </div>
            </a>
        @endforeach
    </div>
</section>
<div class="tab-content">
    <div class="tab-pane active" id="group-all">
        <div class="avatar-grid">
            @foreach($data as $group)
                @foreach($group['goods'] as $goods)
                    @if($goods['in_stock'] > 0)
                    <a href="{{ url("/buy/{$goods['id']}") }}" class="avatar-card category">
                    @else
                    <a href="javascript:void(0);" onclick="sell_out_tip()" class="avatar-card category ribbon-box">
                        <div class="avatar-sold-out">{{ __('hyper.home_out_of_stock') }}</div>
                    @endif
                        <div class="avatar-card-media">
                            <img class="home-img" src="/assets/avatar/images/loading.gif" data-src="{{ picture_ulr($goods['picture']) }}">
                        </div>
                        <div class="avatar-card-body">
                            <p class="avatar-card-name">{{ $goods['gd_name'] }}</p>
                            <div class="avatar-card-meta">
                                <span class="avatar-card-price">{{ __('hyper.global_currency') }}<b>{{ $goods['actual_price'] }}</b></span>
                                <span class="avatar-card-stock">库存 {{ $goods['in_stock'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endforeach
        </div>
    </div>
    @foreach($data as  $index => $group)
        <div class="tab-pane" id="group-{{ $group['id'] }}">
            <div class="avatar-grid">
                @foreach($group['goods'] as $goods)
                    @if($goods['in_stock'] > 0)
                    <a href="{{ url("/buy/{$goods['id']}") }}" class="avatar-card category">
                    @else
                    <a href="javascript:void(0);" onclick="sell_out_tip()" class="avatar-card category ribbon-box">
                        <div class="avatar-sold-out">{{ __('hyper.home_out_of_stock') }}</div>
                    @endif
                        <div class="avatar-card-media">
                            <img class="home-img" src="/assets/avatar/images/loading.gif" data-src="{{ picture_ulr($goods['picture']) }}">
                        </div>
                        <div class="avatar-card-body">
                            <p class="avatar-card-name">{{ $goods['gd_name'] }}</p>
                            <div class="avatar-card-meta">
                                <span class="avatar-card-price">{{ __('hyper.global_currency') }}<b>{{ $goods['actual_price'] }}</b></span>
                                <span class="avatar-card-stock">库存 {{ $goods['in_stock'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
<div class="modal fade" id="notice-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myCenterModalLabel">{{ __('hyper.notice_announcement') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                {!! dujiaoka_config_get('notice') !!}
            </div>
        </div>
    </div>
</div>
@stop 
@section('js')
<script>
    $('#notice-open').click(function() {
        $('#notice-modal').modal();
    });
    $("#search").on("input",function(e){
        var txt = $("#search").val();
        if($.trim(txt)!="") {
            $(".category").hide().filter(":contains('"+txt+"')").show();
        } else {
            $(".category").show();
        }
    });
    function sell_out_tip() {
        $.NotificationApp.send("{{ __('hyper.home_tip') }}","{{ __('hyper.home_sell_out_tip') }}","top-center","rgba(0,0,0,0.2)","info");
    }
</script>
@stop
