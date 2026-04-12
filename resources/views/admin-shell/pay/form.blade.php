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
                        <span>支付名称</span>
                        <input type="text" name="pay_name" value="{{ old('pay_name', $defaults['pay_name']) }}" required>
                    </label>
                    <label>
                        <span>支付标识</span>
                        <input type="text" name="pay_check" value="{{ old('pay_check', $defaults['pay_check']) }}" @if(!$isCreate) readonly @endif required>
                    </label>
                    <label>
                        <span>商户 ID</span>
                        <input type="text" name="merchant_id" value="{{ old('merchant_id', $defaults['merchant_id']) }}" required>
                    </label>
                    <label>
                        <span>支付回调路由</span>
                        <input type="text" name="pay_handleroute" value="{{ old('pay_handleroute', $defaults['pay_handleroute']) }}" required>
                    </label>
                    <label>
                        <span>支付方式</span>
                        <select name="pay_method" required>
                            @foreach($methodOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('pay_method', $defaults['pay_method']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>支付场景</span>
                        <select name="pay_client" required>
                            @foreach($clientOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('pay_client', $defaults['pay_client']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open" value="1" @if(old('is_open', $defaults['is_open'])) checked @endif> 启用该支付通道</span>
                    </label>
                </div>

                <label>
                    <span>商户 KEY</span>
                    <textarea name="merchant_key" rows="6">{{ old('merchant_key', $defaults['merchant_key']) }}</textarea>
                </label>

                <label>
                    <span>商户密钥 / PEM</span>
                    <textarea name="merchant_pem" rows="8" required>{{ old('merchant_pem', $defaults['merchant_pem']) }}</textarea>
                </label>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/pay') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
