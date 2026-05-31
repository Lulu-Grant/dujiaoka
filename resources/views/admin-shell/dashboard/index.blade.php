@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    <style>
        .dashboard-frame {
            display: grid;
            gap: 18px;
        }
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            padding: 32px;
            border-radius: 28px;
            background:
                linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
            color: #f5fff7;
            box-shadow: 0 24px 60px rgba(29, 48, 35, 0.22);
        }
        .dashboard-hero::after {
            content: "";
            position: absolute;
            inset: 16px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            pointer-events: none;
        }
        .dashboard-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(340px, 0.85fr);
            gap: 20px;
            align-items: stretch;
        }
        .dashboard-kicker {
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(245,255,247,0.72);
            margin-bottom: 10px;
        }
        .dashboard-headline {
            margin: 0;
            font-size: 40px;
            line-height: 1.05;
        }
        .dashboard-subline {
            margin: 14px 0 0;
            max-width: 620px;
            color: rgba(245,255,247,0.78);
            font-size: 16px;
            line-height: 1.7;
        }
        .dashboard-hero-meta {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .dashboard-hero-pill,
        .dashboard-health,
        .dashboard-quick-panel,
        .dashboard-operations,
        .dashboard-segment {
            display: grid;
            gap: 12px;
            padding: 18px;
            border-radius: 20px;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.10);
        }
        .dashboard-hero-pill {
            gap: 5px;
        }
        .dashboard-hero-pill label {
            color: rgba(245,255,247,0.66);
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .dashboard-hero-pill strong {
            font-size: 24px;
        }
        .dashboard-hero-panel {
            display: grid;
            gap: 14px;
        }
        .dashboard-hero-strip {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 14px;
            align-items: center;
            padding: 18px 20px;
            border-radius: 24px;
            background: rgba(0, 0, 0, 0.14);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .dashboard-health-ring {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at center, rgba(32,56,39,0.25) 0 52%, transparent 53%),
                conic-gradient(from 180deg, rgba(255,255,255,0.92) 0 calc(var(--score) * 1%), rgba(255,255,255,0.14) 0);
            box-shadow: 0 12px 22px rgba(0, 0, 0, 0.18);
        }
        .dashboard-health-ring span {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(27, 44, 31, 0.92);
            font-size: 18px;
            font-weight: 800;
        }
        .dashboard-health-copy {
            display: grid;
            gap: 4px;
        }
        .dashboard-health-copy small {
            color: rgba(245,255,247,0.65);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .dashboard-health-copy strong {
            font-size: 20px;
        }
        .dashboard-health-copy p {
            margin: 0;
            color: rgba(245,255,247,0.76);
            font-size: 14px;
            line-height: 1.6;
        }
        .dashboard-health-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .dashboard-health-badge::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
            box-shadow: 0 0 0 4px rgba(255,255,255,0.10);
        }
        .dashboard-health-badge.good {
            color: #7dff9e;
            background: rgba(71, 158, 93, 0.18);
        }
        .dashboard-health-badge.warning {
            color: #ffe07a;
            background: rgba(224, 171, 52, 0.18);
        }
        .dashboard-health-badge.danger {
            color: #ff9ca9;
            background: rgba(199, 92, 111, 0.18);
        }
        .dashboard-quick-panel {
            background: rgba(255,255,255,0.07);
        }
        .dashboard-quick-panel h3,
        .dashboard-operations h3,
        .dashboard-segment-header {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }
        .dashboard-quick-list {
            display: grid;
            gap: 10px;
        }
        .dashboard-quick-link {
            display: grid;
            gap: 4px;
            padding: 12px 14px;
            border-radius: 16px;
            color: inherit;
            text-decoration: none;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.06);
            transition: transform 160ms ease, background 160ms ease, border-color 160ms ease;
        }
        .dashboard-quick-link:hover {
            transform: translateY(-1px);
            background: rgba(255,255,255,0.11);
            border-color: rgba(255,255,255,0.16);
        }
        .dashboard-quick-link strong {
            font-size: 14px;
        }
        .dashboard-quick-link span {
            color: rgba(245,255,247,0.72);
            font-size: 13px;
            line-height: 1.55;
        }
        .dashboard-panel-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
        .dashboard-playbook-grid,
        .dashboard-shortcut-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .dashboard-health,
        .dashboard-operations {
            background: var(--panel);
            border: 1px solid var(--line);
            box-shadow: 0 14px 40px rgba(32, 51, 38, 0.06);
        }
        .dashboard-health__list,
        .dashboard-operations__list {
            display: grid;
            gap: 10px;
        }
        .dashboard-health__item,
        .dashboard-operations__item {
            display: grid;
            gap: 4px;
            padding: 12px 0;
            border-bottom: 1px dashed rgba(34,49,39,0.08);
        }
        .dashboard-health__item:last-child,
        .dashboard-operations__item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .dashboard-health__item span,
        .dashboard-operations__item span {
            color: var(--muted);
            font-size: 13px;
        }
        .dashboard-health__item strong,
        .dashboard-operations__item strong {
            font-size: 18px;
        }
        .dashboard-shortcut-card,
        .dashboard-playbook-card {
            display: grid;
            gap: 12px;
            padding: 20px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #ffffff;
            box-shadow: 0 16px 36px rgba(32, 51, 38, 0.08);
        }
        .dashboard-shortcut-card h3,
        .dashboard-playbook-card h3 {
            margin: 0;
            font-size: 18px;
        }
        .dashboard-action-grid {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .dashboard-action-card {
            display: grid;
            gap: 10px;
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #ffffff;
            box-shadow: 0 14px 32px rgba(32, 51, 38, 0.06);
        }
        .dashboard-action-card h3 {
            margin: 0;
            font-size: 18px;
        }
        .dashboard-action-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
        }
        .dashboard-action-card .button {
            width: fit-content;
        }
        .dashboard-shortcut-card p,
        .dashboard-playbook-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }
        .dashboard-shortcut-list,
        .dashboard-playbook-list {
            display: grid;
            gap: 10px;
        }
        .dashboard-shortcut-link {
            display: grid;
            gap: 4px;
            padding: 12px 14px;
            border-radius: 16px;
            background: #f9fafb;
            border: 1px solid var(--line);
            color: inherit;
            text-decoration: none;
            transition: transform 160ms ease, background 160ms ease, border-color 160ms ease;
        }
        .dashboard-shortcut-link:hover {
            transform: translateY(-1px);
            background: var(--shell-accent-soft);
            border-color: rgba(37, 99, 235, 0.22);
        }
        .dashboard-shortcut-link strong {
            font-size: 14px;
        }
        .dashboard-shortcut-link span {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .dashboard-playbook-step {
            display: grid;
            gap: 4px;
            padding: 12px 0;
            border-bottom: 1px dashed rgba(34,49,39,0.08);
        }
        .dashboard-playbook-step:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .dashboard-playbook-step span {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }
        .dashboard-card-grid {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .dashboard-card {
            position: relative;
            overflow: hidden;
            padding: 20px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #ffffff;
            box-shadow: 0 16px 36px rgba(32, 51, 38, 0.08);
        }
        .dashboard-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 6px;
            background: var(--card-accent);
        }
        .dashboard-card-eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }
        .dashboard-card h3 {
            margin: 10px 0 6px;
            font-size: 22px;
        }
        .dashboard-card-value {
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
        }
        .dashboard-card p {
            margin: 12px 0 0;
            color: var(--muted);
        }
        .dashboard-section-title {
            margin: 8px 0 0;
            font-size: 18px;
            font-weight: 800;
        }
        .dashboard-section-copy {
            margin: 6px 0 0;
            color: var(--muted);
            line-height: 1.65;
        }
        .dashboard-segment-grid {
            margin-top: 6px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .dashboard-segment-header {
            padding: 0;
            border-bottom: 1px solid var(--line);
            padding-bottom: 12px;
        }
        .dashboard-segment-list {
            padding: 6px 0 0;
            display: grid;
            gap: 10px;
        }
        .dashboard-segment-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px dashed rgba(34,49,39,0.08);
        }
        .dashboard-segment-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .dashboard-segment-row span {
            color: var(--muted);
        }
        .dashboard-segment-row strong {
            font-size: 18px;
        }
        .accent-lime { --card-accent: #2c8f57; }
        .accent-amber { --card-accent: #d29922; }
        .accent-teal { --card-accent: #1d9a8a; }
        .accent-rose { --card-accent: #d15a6b; }
        @media (max-width: 960px) {
            .dashboard-hero-grid {
                grid-template-columns: 1fr;
            }
            .dashboard-headline {
                font-size: 34px;
            }
            .dashboard-panel-grid {
                grid-template-columns: 1fr;
            }
            .dashboard-hero-meta {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-frame {
            --panel: #ffffff;
            --line: var(--shell-line);
            --muted: var(--shell-muted);
            gap: 18px;
        }

        .dashboard-hero {
            padding: 22px;
            border-radius: 12px;
            background: #ffffff;
            color: var(--shell-ink);
            border: 1px solid var(--shell-line);
            box-shadow: var(--shell-shadow);
        }

        .dashboard-hero::after {
            content: none;
        }

        .dashboard-hero-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .dashboard-kicker {
            color: var(--shell-muted);
            letter-spacing: 0.08em;
        }

        .dashboard-headline {
            font-size: 28px;
            line-height: 1.25;
            letter-spacing: 0;
        }

        .dashboard-subline {
            max-width: 760px;
            color: var(--shell-muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .dashboard-hero-meta {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .dashboard-hero-pill,
        .dashboard-health,
        .dashboard-quick-panel,
        .dashboard-operations,
        .dashboard-segment,
        .dashboard-shortcut-card,
        .dashboard-playbook-card,
        .dashboard-action-card,
        .dashboard-card {
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid var(--shell-line);
            box-shadow: var(--shell-shadow);
            backdrop-filter: none;
        }

        .dashboard-hero-pill {
            padding: 14px;
            gap: 6px;
            background: #f9fafb;
        }

        .dashboard-hero-pill label {
            color: var(--shell-muted);
            letter-spacing: 0.06em;
        }

        .dashboard-hero-pill strong {
            color: var(--shell-ink);
            font-size: 22px;
        }

        .dashboard-hero-strip {
            border-radius: 10px;
            background: #f9fafb;
            border: 1px solid var(--shell-line);
        }

        .dashboard-health-ring {
            background: conic-gradient(from 180deg, var(--shell-accent) 0 calc(var(--score) * 1%), #e5e7eb 0);
            box-shadow: none;
        }

        .dashboard-health-ring span {
            background: #ffffff;
            color: var(--shell-ink);
        }

        .dashboard-health-copy small,
        .dashboard-health-copy p,
        .dashboard-quick-link span {
            color: var(--shell-muted);
        }

        .dashboard-health-copy strong,
        .dashboard-quick-panel h3,
        .dashboard-operations h3,
        .dashboard-segment-header {
            color: var(--shell-ink);
        }

        .dashboard-health-badge {
            border-radius: 999px;
            letter-spacing: 0;
        }

        .dashboard-health-badge.good {
            color: #15803d;
            background: rgba(22, 163, 74, 0.1);
        }

        .dashboard-health-badge.warning {
            color: #c2410c;
            background: var(--shell-amber-soft);
        }

        .dashboard-health-badge.danger {
            color: var(--shell-danger);
            background: rgba(220, 38, 38, 0.1);
        }

        .dashboard-health-badge::before {
            box-shadow: none;
        }

        .dashboard-quick-link,
        .dashboard-shortcut-link {
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid var(--shell-line);
        }

        .dashboard-quick-link:hover,
        .dashboard-shortcut-link:hover {
            transform: none;
            background: var(--shell-accent-soft);
            border-color: rgba(37, 99, 235, 0.22);
        }

        .dashboard-shortcut-card,
        .dashboard-playbook-card,
        .dashboard-action-card,
        .dashboard-card {
            padding: 16px;
        }

        .dashboard-section-title {
            font-size: 17px;
            letter-spacing: 0;
        }

        .dashboard-section-copy,
        .dashboard-action-card p,
        .dashboard-shortcut-card p,
        .dashboard-playbook-card p,
        .dashboard-playbook-step span,
        .dashboard-segment-row span,
        .dashboard-card p,
        .dashboard-health__item span,
        .dashboard-operations__item span {
            color: var(--shell-muted);
        }

        .dashboard-action-card .button {
            width: fit-content;
        }

        .dashboard-card::before {
            width: 4px;
        }

        .dashboard-card-value {
            font-size: 28px;
        }

        .accent-lime { --card-accent: #2563eb; }
        .accent-amber { --card-accent: #f97316; }
        .accent-teal { --card-accent: #0ea5e9; }
        .accent-rose { --card-accent: #dc2626; }

        @media (max-width: 1180px) {
            .dashboard-hero-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 960px) {
            .dashboard-headline {
                font-size: 24px;
            }
        }
    </style>

    <div class="dashboard-frame">
    <section class="dashboard-hero">
        <div class="dashboard-hero-grid">
            <div>
                <div class="dashboard-kicker">今日概览</div>
                <h2 class="dashboard-headline">订单与支付状态</h2>
                <p class="dashboard-subline">快速查看今日成交、订单数量、完成情况和当前系统状态。</p>
                <div class="dashboard-hero-meta">
                    <div class="dashboard-hero-pill">
                        <label>今日成功率</label>
                        <strong>{{ $hero['success_rate'] }}%</strong>
                    </div>
                    <div class="dashboard-hero-pill">
                        <label>今日订单数</label>
                        <strong>{{ $hero['order_count'] }}</strong>
                    </div>
                    <div class="dashboard-hero-pill">
                        <label>今日销售额</label>
                        <strong>{{ $hero['sales_total'] }}</strong>
                    </div>
                    <div class="dashboard-hero-pill">
                        <label>已完成订单</label>
                        <strong>{{ $hero['completed_count'] }}</strong>
                    </div>
                </div>
                <div style="margin-top: 18px;">
                    <div class="dashboard-health-badge {{ $health['tone'] }}">{{ $health['label'] }}</div>
                    <p class="dashboard-subline" style="margin-top: 10px;">{{ $health['note'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section>
        <h3 class="dashboard-section-title">运营视图</h3>
        <p class="dashboard-section-copy">优先关注异常订单、待处理订单和支付状态。</p>
        <div class="dashboard-panel-grid">
            <div class="dashboard-health">
                <h3>系统健康状态</h3>
                <div class="dashboard-health__list">
                    <div class="dashboard-health__item">
                        <span>健康评分</span>
                        <strong>{{ $health['score'] }}/100</strong>
                    </div>
                    <div class="dashboard-health__item">
                        <span>状态判定</span>
                        <strong>{{ $health['label'] }}</strong>
                    </div>
                    <div class="dashboard-health__item">
                        <span>今日成功率</span>
                        <strong>{{ $hero['success_rate'] }}%</strong>
                    </div>
                </div>
            </div>
            <div class="dashboard-operations">
                <h3>重点信号</h3>
                <div class="dashboard-operations__list">
                    @foreach($operations as $operation)
                        <div class="dashboard-operations__item">
                            <span>{{ $operation['label'] }}</span>
                            <strong>{{ $operation['value'] }}</strong>
                            <span>{{ $operation['note'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="dashboard-playbook-card">
                <h3>本日操作建议</h3>
                <div class="dashboard-playbook-list">
                    @foreach($operator_brief as $brief)
                        <div class="dashboard-playbook-step">
                            <strong>{{ $brief['title'] }}</strong>
                            <span>{{ $brief['description'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-card-grid">
        @foreach($cards as $card)
            <article class="dashboard-card accent-{{ $card['accent'] }}">
                <div class="dashboard-card-eyebrow">{{ $card['eyebrow'] }}</div>
                <h3>{{ $card['title'] }}</h3>
                <div class="dashboard-card-value">{{ $card['value'] }}</div>
                <p>{{ $card['description'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="dashboard-segment-grid">
        @foreach($segments as $segment)
            <div class="dashboard-segment">
                <div class="dashboard-segment-header">{{ $segment['title'] }}</div>
                <div class="dashboard-segment-list">
                    @foreach($segment['items'] as $item)
                        <div class="dashboard-segment-row">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
    </div>
@endsection
