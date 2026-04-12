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
