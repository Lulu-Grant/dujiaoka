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
                        <span>邮件驱动</span>
                        <input type="text" name="driver" value="{{ old('driver', $defaults['driver']) }}">
                    </label>
                    <label>
                        <span>SMTP 主机</span>
                        <input type="text" name="host" value="{{ old('host', $defaults['host']) }}">
                    </label>
                    <label>
                        <span>SMTP 端口</span>
                        <input type="number" min="1" max="65535" name="port" value="{{ old('port', $defaults['port']) }}">
                    </label>
                    <label>
                        <span>账号</span>
                        <input type="text" name="username" value="{{ old('username', $defaults['username']) }}">
                    </label>
                    <label>
                        <span>密码</span>
                        <input type="text" name="password" value="{{ old('password', $defaults['password']) }}">
                    </label>
                    <label>
                        <span>协议</span>
                        <input type="text" name="encryption" value="{{ old('encryption', $defaults['encryption']) }}">
                    </label>
                    <label>
                        <span>发件地址</span>
                        <input type="email" name="from_address" value="{{ old('from_address', $defaults['from_address']) }}">
                    </label>
                    <label>
                        <span>发件名称</span>
                        <input type="text" name="from_name" value="{{ old('from_name', $defaults['from_name']) }}">
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">保存邮件配置</button>
                    <a class="button secondary" href="{{ admin_url('v2/system-setting') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
