@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @if(session('status'))
        <div class="panel">
            <div class="panel-body">
                <div class="notice success">{{ session('status') }}</div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="panel">
            <div class="panel-body">
                <div class="notice error">{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    @if(!empty($sourcePay))
        <div class="panel">
            <div class="panel-body">
                <div class="notice info">
                    正在复制支付通道 <strong>{{ $sourcePay->pay_name }}</strong>（{{ $sourcePay->pay_check }}）。
                    支付名称、商户 ID、支付方式、支付场景、回调路由和启用状态会被预填，支付标识、商户 KEY 和商户 PEM 需要重新确认。
                </div>
            </div>
        </div>
    @endif

    <div class="panel">
        <div class="panel-body">
            <div class="page-kicker">维护摘要</div>
            <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">{{ $context['summaryTitle'] }}</h2>
            <p class="page-description">{{ $context['summaryDescription'] }}</p>

            <div class="detail-grid" style="margin-top: 20px;">
                @foreach($context['summaryItems'] as $item)
                    <div class="detail-item">
                        <div class="detail-label">{{ $item['label'] }}</div>
                        <div class="detail-value">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="meta" style="margin-top: 18px;">
                可编辑字段：{{ implode('、', $context['editableFields']) }}
            </div>
            <div class="meta" style="margin-top: 8px;">
                {{ $context['notice'] }}
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                @foreach($sections as $section)
                    @include('admin-shell.pay.partials.form-section', ['section' => $section])
                @endforeach

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/pay') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
