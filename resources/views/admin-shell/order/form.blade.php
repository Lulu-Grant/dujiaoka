@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @php
        $basicSummary = $context['summarySections'][0] ?? [];
        $maintenanceSummary = $context['summarySections'][2] ?? [];
        $currentStatus = $basicSummary['items'][3]['value'] ?? '未知';
    @endphp

    @if(session('status'))
        <div class="panel">
            <div class="panel-body">
                <div class="notice success">{{ session('status') }}</div>
            </div>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="panel">
            <div class="panel-body">
                <div class="notice error">{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <div class="panel">
        <div class="panel-body">
            <div class="page-kicker">维护摘要</div>
            <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">{{ $context['summaryTitle'] }}</h2>
            <p class="page-description">{{ $context['summaryDescription'] }}</p>

            @if(!empty($context['summarySections']))
                <div style="display: grid; gap: 16px; margin-top: 20px;">
                    @foreach($context['summarySections'] as $section)
                        <section class="panel" style="margin: 0; box-shadow: none; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="panel-body">
                                <div class="page-kicker" style="margin-bottom: 6px;">{{ $section['title'] }}</div>
                                @if(!empty($section['description']))
                                    <p class="page-description" style="margin-bottom: 14px;">{{ $section['description'] }}</p>
                                @endif
                                <div class="detail-grid">
                                    @foreach($section['items'] as $item)
                                        <div class="detail-item">
                                            <div class="detail-label">{{ $item['label'] }}</div>
                                            <div class="detail-value">{{ $item['value'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
            @elseif(!empty($context['summaryItems']))
                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['summaryItems'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">{{ $item['label'] }}</div>
                            <div class="detail-value">{{ $item['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

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

                <div class="filters">
                    <label>
                        <span>订单号</span>
                        <input type="text" value="{{ $order->order_sn }}" disabled>
                    </label>
                    <label>
                        <span>订单标题</span>
                        <input type="text" value="{{ $order->title }}" disabled>
                    </label>
                    <label>
                        <span>邮箱</span>
                        <input type="text" value="{{ $order->email }}" disabled>
                    </label>
                    <label>
                        <span>关联商品</span>
                        <input type="text" value="{{ optional($order->goods)->gd_name ?: '未关联商品' }}" disabled>
                    </label>
                    <label>
                        <span>支付通道</span>
                        <input type="text" value="{{ optional($order->pay)->pay_name ?: '未选择支付' }}" disabled>
                    </label>
                    <label>
                        <span>订单状态</span>
                        <input type="text" value="{{ $currentStatus }}" disabled>
                    </label>
                    <label>
                        <span>查询密码</span>
                        <input type="text" value="{{ $defaults['search_pwd'] }}" disabled>
                    </label>
                </div>

                <div class="filters">
                    <label>
                        <span>订单标题</span>
                        <input type="text" name="title" value="{{ old('title', $defaults['title']) }}" required>
                    </label>
                    <label>
                        <span>订单状态</span>
                        <select name="status" required>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('status', $defaults['status']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>订单类型</span>
                        <select name="type" required>
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('type', $defaults['type']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>查询密码</span>
                        <input type="text" name="search_pwd" value="{{ old('search_pwd', $defaults['search_pwd']) }}" required>
                    </label>
                </div>

                <div class="meta" style="margin-top: 10px;">
                    当前订单状态：{{ $currentStatus }}。{{ $maintenanceSummary['description'] ?? '仅在确认需要人工修正时再保存。' }}
                </div>

                <label>
                    <span>订单附加信息</span>
                    <textarea name="info" rows="10">{{ old('info', $defaults['info']) }}</textarea>
                </label>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/order') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
