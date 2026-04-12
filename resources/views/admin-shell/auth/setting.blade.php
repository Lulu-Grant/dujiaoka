@extends('admin-shell.layout')

@section('content')
    <header class="page-header">
        <div class="page-header__copy">
            <span class="page-kicker">{{ $header['kicker'] }}</span>
            <h1 class="page-title">{{ $header['title'] }}</h1>
            <p class="page-description">{{ $header['description'] }}</p>
        </div>
        <div class="page-header__aside">
            <div class="page-meta">{{ $header['meta'] }}</div>
            <div class="page-actions">
                @foreach($header['actions'] as $action)
                    <a class="button{{ ($action['variant'] ?? 'primary') === 'secondary' ? ' secondary' : '' }}" href="{{ $action['href'] }}">
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </header>

    @if(session('status'))
        <div class="notice success" style="margin-bottom: 18px;">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="notice error" style="margin-bottom: 18px;">
            <strong>保存失败：</strong>
            <ul style="margin: 10px 0 0 18px; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="detail-grid" style="margin-bottom: 18px;">
        @foreach($context as $item)
            <div class="detail-item">
                <div class="detail-label">{{ $item['label'] }}</div>
                <div class="detail-value">{{ $item['value'] }}</div>
            </div>
        @endforeach
    </div>

    <form method="post" action="{{ admin_url('auth/setting') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <section class="panel">
            <div class="panel-body">
                <div class="page-header" style="padding-bottom: 14px; margin-bottom: 18px;">
                    <div class="page-header__copy">
                        <span class="page-kicker">Profile</span>
                        <h2 class="page-title" style="font-size: 26px;">基础资料</h2>
                        <p class="page-description">这里承接日常高频的个人账号维护动作。登录账号只读，昵称和头像可直接修改。</p>
                    </div>
                </div>

                <div class="filters">
                    <label>
                        <span>登录账号</span>
                        <input type="text" value="{{ $defaults['username'] }}" disabled>
                    </label>

                    <label>
                        <span>显示昵称</span>
                        <input type="text" name="name" value="{{ old('name', $defaults['name']) }}" required>
                    </label>

                    <label>
                        <span>头像文件</span>
                        <input type="file" name="avatar" accept="image/*">
                        <small class="meta">保留为空则维持现状，上传后会保存到后台上传目录。</small>
                    </label>
                </div>
            </div>
        </section>

        <section class="panel" style="margin-top: 18px;">
            <div class="panel-body">
                <div class="page-header" style="padding-bottom: 14px; margin-bottom: 18px;">
                    <div class="page-header__copy">
                        <span class="page-kicker">Security</span>
                        <h2 class="page-title" style="font-size: 26px;">密码更新</h2>
                        <p class="page-description">只有在填写新密码时才会触发密码变更，并继续强制校验旧密码。</p>
                    </div>
                </div>

                <div class="filters">
                    <label>
                        <span>旧密码</span>
                        <input type="password" name="old_password" placeholder="如需改密码，请先输入旧密码">
                    </label>

                    <label>
                        <span>新密码</span>
                        <input type="password" name="password" placeholder="留空则不修改">
                    </label>

                    <label>
                        <span>确认新密码</span>
                        <input type="password" name="password_confirmation" placeholder="再次输入新密码">
                    </label>
                </div>
            </div>
        </section>

        <div class="button-row" style="margin-top: 18px;">
            <button type="submit" class="button">保存账号设置</button>
            <a href="{{ admin_url('v2/dashboard') }}" class="button secondary">返回后台总览</a>
        </div>
    </form>
@endsection
