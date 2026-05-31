<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111827">
    <title>{{ $title ?? '独角数卡西瓜版后台登录' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/avatar/css/admin-shell.css') }}">
</head>
<body class="admin-shell admin-shell-auth">
<div class="auth-shell">
    <section class="auth-card-wrap">
        <div class="auth-card">
            <div class="auth-card__header">
                <div class="auth-card__brand">
                    <img src="/assets/avatar/images/dujiaoka-xigua.png" alt="独角数卡西瓜版">
                    <span>独角数卡西瓜版</span>
                </div>
                <span class="auth-card__kicker">登录</span>
                <h2>进入后台控制中心</h2>
                <p>使用管理员账号登录，默认进入后台总览页。</p>
            </div>

            @if($errors->any())
                <div class="auth-alert auth-alert--danger">
                    <strong>登录失败</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ admin_url('auth/login') }}" class="auth-form">
                @csrf
                <label class="auth-field">
                    <span>管理员账号</span>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="请输入管理员账号" required autofocus>
                </label>

                <label class="auth-field">
                    <span>登录密码</span>
                    <input type="password" name="password" placeholder="请输入登录密码" required>
                </label>

                <label class="auth-check">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>记住我</span>
                </label>

                <button type="submit" class="auth-submit">进入后台</button>
            </form>
        </div>
    </section>
</div>
</body>
</html>
