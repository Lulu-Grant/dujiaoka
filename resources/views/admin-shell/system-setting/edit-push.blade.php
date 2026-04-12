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
                        <span><input type="checkbox" name="is_open_server_jiang" value="1" @if(old('is_open_server_jiang', $defaults['is_open_server_jiang'])) checked @endif> 开启 Server 酱推送</span>
                    </label>
                    <label>
                        <span>Server 酱 Token</span>
                        <input type="text" name="server_jiang_token" value="{{ old('server_jiang_token', $defaults['server_jiang_token']) }}">
                    </label>

                    <label>
                        <span><input type="checkbox" name="is_open_telegram_push" value="1" @if(old('is_open_telegram_push', $defaults['is_open_telegram_push'])) checked @endif> 开启 Telegram 推送</span>
                    </label>
                    <label>
                        <span>Telegram Bot Token</span>
                        <input type="text" name="telegram_bot_token" value="{{ old('telegram_bot_token', $defaults['telegram_bot_token']) }}">
                    </label>
                    <label>
                        <span>Telegram 用户 ID</span>
                        <input type="text" name="telegram_userid" value="{{ old('telegram_userid', $defaults['telegram_userid']) }}">
                    </label>

                    <label>
                        <span><input type="checkbox" name="is_open_bark_push" value="1" @if(old('is_open_bark_push', $defaults['is_open_bark_push'])) checked @endif> 开启 Bark 推送</span>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open_bark_push_url" value="1" @if(old('is_open_bark_push_url', $defaults['is_open_bark_push_url'])) checked @endif> 推送订单 URL</span>
                    </label>
                    <label>
                        <span>Bark 服务器</span>
                        <input type="text" name="bark_server" value="{{ old('bark_server', $defaults['bark_server']) }}">
                    </label>
                    <label>
                        <span>Bark Token</span>
                        <input type="text" name="bark_token" value="{{ old('bark_token', $defaults['bark_token']) }}">
                    </label>

                    <label>
                        <span><input type="checkbox" name="is_open_qywxbot_push" value="1" @if(old('is_open_qywxbot_push', $defaults['is_open_qywxbot_push'])) checked @endif> 开启企业微信机器人推送</span>
                    </label>
                    <label>
                        <span>企业微信机器人 Key</span>
                        <input type="text" name="qywxbot_key" value="{{ old('qywxbot_key', $defaults['qywxbot_key']) }}">
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">保存通知推送配置</button>
                    <a class="button secondary" href="{{ admin_url('v2/system-setting') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
