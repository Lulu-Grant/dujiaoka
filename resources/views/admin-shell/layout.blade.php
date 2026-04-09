<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? '独角数卡西瓜版后台壳样板' }}</title>
    <style>
        :root {
            --bg: #f4f7f1;
            --panel: #ffffff;
            --line: #d9e1d2;
            --ink: #223127;
            --muted: #66756b;
            --accent: #2c8f57;
            --accent-soft: #e7f6eb;
            --warn: #b7791f;
            --danger: #b63e3e;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "PingFang SC", "Noto Sans SC", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top right, rgba(255, 198, 69, 0.18), transparent 26%),
                radial-gradient(circle at top left, rgba(84, 184, 127, 0.18), transparent 24%),
                var(--bg);
        }
        a { color: inherit; text-decoration: none; }
        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }
        .sidebar {
            padding: 28px 22px;
            background: linear-gradient(180deg, #1f3425 0%, #294832 100%);
            color: #f4fff6;
        }
        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 28px;
        }
        .brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }
        .brand-title {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.2;
        }
        .brand-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: rgba(244, 255, 246, 0.72);
        }
        .nav-label {
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(244, 255, 246, 0.55);
            margin-bottom: 10px;
        }
        .nav-list {
            display: grid;
            gap: 10px;
        }
        .nav-item {
            display: block;
            padding: 11px 13px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.08);
            font-size: 14px;
        }
        .nav-item.active {
            background: rgba(255, 255, 255, 0.18);
            font-weight: 600;
        }
        .content {
            padding: 28px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: end;
            margin-bottom: 22px;
        }
        .page-kicker {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 700;
            margin-bottom: 8px;
        }
        .page-title {
            margin: 0;
            font-size: 30px;
            line-height: 1.1;
        }
        .page-description {
            margin: 8px 0 0;
            color: var(--muted);
            max-width: 720px;
        }
        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 14px 40px rgba(32, 51, 38, 0.07);
        }
        .panel + .panel {
            margin-top: 18px;
        }
        .panel-body {
            padding: 22px;
        }
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            align-items: end;
        }
        label {
            display: grid;
            gap: 6px;
            font-size: 13px;
            color: var(--muted);
        }
        input, select {
            width: 100%;
            padding: 11px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fbfdf9;
            color: var(--ink);
        }
        .button-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 12px;
            border: 0;
            background: var(--accent);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        .button.secondary {
            background: var(--accent-soft);
            color: var(--accent);
        }
        .table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }
        th {
            font-size: 12px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .pill.open { background: #e7f6eb; color: #1d7c49; }
        .pill.closed { background: #fce8e8; color: #b63e3e; }
        .pill.trashed { background: #fff3dd; color: var(--warn); }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .detail-item {
            padding: 16px;
            border-radius: 16px;
            background: #f9fcf7;
            border: 1px solid var(--line);
        }
        .detail-label {
            font-size: 12px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .detail-value {
            font-size: 16px;
            font-weight: 600;
        }
        .meta {
            color: var(--muted);
            font-size: 13px;
        }
        .empty {
            padding: 28px;
            text-align: center;
            color: var(--muted);
        }
        .pagination {
            margin-top: 18px;
        }
        @media (max-width: 960px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { padding-bottom: 18px; }
            .content { padding: 18px; }
            .page-header { align-items: start; flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand">
            <img src="/assets/avatar/images/dujiaoka-xigua.png" alt="独角数卡西瓜版">
            <div>
                <div class="brand-title">独角数卡西瓜版</div>
                <div class="brand-subtitle">后台壳样板</div>
            </div>
        </div>
        <div class="nav-label">First Batch</div>
        <nav class="nav-list">
            <a class="nav-item{{ request()->is(config('admin.route.prefix').'/v2/goods-group*') ? ' active' : '' }}" href="{{ admin_url('v2/goods-group') }}">商品分类管理</a>
            <a class="nav-item{{ request()->is(config('admin.route.prefix').'/v2/emailtpl*') ? ' active' : '' }}" href="{{ admin_url('v2/emailtpl') }}">邮件模板管理</a>
            <span class="nav-item">支付通道管理</span>
        </nav>
    </aside>
    <main class="content">
        @yield('content')
    </main>
</div>
</body>
</html>
