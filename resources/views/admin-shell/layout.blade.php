<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111827">
    <title>{{ $title ?? '独角数卡西瓜版管理后台' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/avatar/css/admin-shell.css') }}">
</head>
<body class="admin-shell">
<div class="shell-frame">
    <div class="shell">
        <aside class="sidebar">
            <div class="sidebar-surface">
                <div class="brand">
                    <img src="/assets/avatar/images/dujiaoka-xigua.png" alt="独角数卡西瓜版">
                    <div class="brand-copy">
                        <div class="brand-title">独角数卡西瓜版</div>
                        <div class="brand-subtitle">管理后台</div>
                    </div>
                </div>

                <div class="sidebar-note">
                    <span class="sidebar-note__label">控制台</span>
                    <strong>运营管理</strong>
                    <p>统一管理商品、订单、支付和系统配置。</p>
                </div>

                <div class="nav-label">{{ \App\Service\AdminShellResourceRegistry::navigationSectionLabel() }}</div>
                <nav class="nav-list">
                    <a class="nav-item{{ request()->is(config('admin.route.prefix').'/v2/dashboard') ? ' active' : '' }}" href="{{ admin_url('v2/dashboard') }}">
                        <span class="nav-item__marker"></span>
                        <span class="nav-item__text">后台总览</span>
                    </a>
                    @foreach(\App\Service\AdminShellResourceRegistry::navigationItems() as $item)
                        <a class="nav-item{{ request()->is($item['active_pattern']) ? ' active' : '' }}" href="{{ $item['href'] }}">
                            <span class="nav-item__marker"></span>
                            <span class="nav-item__text">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="sidebar-footer">
                    <span>简洁后台界面</span>
                    <div class="sidebar-footer__links">
                        <a href="{{ admin_url('auth/setting') }}">账号设置</a>
                        <a href="{{ admin_url('auth/logout') }}">退出登录</a>
                    </div>
                </div>
            </div>
        </aside>

        <main class="content">
            <div class="content-shell">
                @yield('content')
            </div>
        </main>
    </div>
</div>
</body>
</html>
