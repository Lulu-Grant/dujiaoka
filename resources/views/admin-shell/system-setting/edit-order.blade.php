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
                        <span>订单过期时间（分钟）</span>
                        <input type="number" min="1" max="1440" name="order_expire_time" value="{{ old('order_expire_time', $defaults['order_expire_time']) }}">
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open_img_code" value="1" @if(old('is_open_img_code', $defaults['is_open_img_code'])) checked @endif> 开启图形验证码</span>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open_search_pwd" value="1" @if(old('is_open_search_pwd', $defaults['is_open_search_pwd'])) checked @endif> 开启订单查询密码</span>
                    </label>
                </div>

                <div class="panel" style="margin-top: 16px; background: #f9fcf7;">
                    <div class="panel-body">
                        <strong>说明</strong>
                        <p style="margin: 10px 0 0; color: var(--muted); line-height: 1.75;">
                            这张页面专门承接订单行为相关配置。当前只保留订单过期时间、图形验证码和订单查询密码，方便后续把支付前后行为进一步收口到独立配置页。
                        </p>
                    </div>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">保存订单行为配置</button>
                    <a class="button secondary" href="{{ admin_url('v2/system-setting') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
