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
                        <span>收件人</span>
                        <input type="email" name="to" value="{{ old('to', $defaults['to']) }}" placeholder="name@example.com">
                    </label>
                    <label>
                        <span>邮件标题</span>
                        <input type="text" name="title" value="{{ old('title', $defaults['title']) }}">
                    </label>
                </div>

                <label style="margin-top: 14px;">
                    <span>邮件内容</span>
                    <textarea name="body" rows="10">{{ old('body', $defaults['body']) }}</textarea>
                </label>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">发送测试邮件</button>
                    <a class="button secondary" href="{{ admin_url('v2/email-test') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="page-kicker">Runtime Mail Config</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">邮件驱动</div>
                    <div class="detail-value">{{ $runtimeSummary['driver'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">SMTP 主机</div>
                    <div class="detail-value">{{ $runtimeSummary['host'] ?: '未配置' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">SMTP 端口</div>
                    <div class="detail-value">{{ $runtimeSummary['port'] ?: '未配置' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">发件地址</div>
                    <div class="detail-value">{{ $runtimeSummary['from_address'] ?: '未配置' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">发件名称</div>
                    <div class="detail-value">{{ $runtimeSummary['from_name'] ?: '未配置' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">配置状态</div>
                    <div class="detail-value">{{ $runtimeSummary['configured'] ? '可测试发送' : '建议先补齐配置' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
