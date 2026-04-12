@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    <style>
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            padding: 28px;
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(255, 208, 86, 0.26), transparent 24%),
                radial-gradient(circle at bottom left, rgba(75, 185, 125, 0.22), transparent 28%),
                linear-gradient(135deg, #203827 0%, #2d4a34 48%, #1d3023 100%);
            color: #f5fff7;
            box-shadow: 0 24px 60px rgba(29, 48, 35, 0.22);
        }
        .dashboard-hero::after {
            content: "";
            position: absolute;
            inset: 18px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 22px;
            pointer-events: none;
        }
        .dashboard-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 18px;
            align-items: center;
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
            font-size: 42px;
            line-height: 1.05;
        }
        .dashboard-subline {
            margin: 12px 0 0;
            max-width: 620px;
            color: rgba(245,255,247,0.78);
        }
        .dashboard-hero-stats {
            display: grid;
            gap: 12px;
        }
        .dashboard-hero-pill {
            display: grid;
            gap: 6px;
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255,255,255,0.09);
            backdrop-filter: blur(8px);
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
            background: linear-gradient(180deg, #ffffff 0%, #f8fbf6 100%);
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
        .dashboard-segment-grid {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .dashboard-segment {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: 0 14px 40px rgba(32, 51, 38, 0.06);
            overflow: hidden;
        }
        .dashboard-segment-header {
            padding: 18px 20px 12px;
            border-bottom: 1px solid var(--line);
            font-size: 15px;
            font-weight: 700;
        }
        .dashboard-segment-list {
            padding: 14px 20px 18px;
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
        }
    </style>

    <section class="dashboard-hero">
        <div class="dashboard-hero-grid">
            <div>
                <div class="dashboard-kicker">Xigua Control Surface</div>
                <h2 class="dashboard-headline">今天的运营脉搏一眼就能看到。</h2>
                <p class="dashboard-subline">我们先用后台壳承接首页统计，把成功率、销售额、订单完成数和支付分布从旧 Dcat 卡片平移到普通 Laravel 页面里，后面再继续把更多后台高频页接进来。</p>
            </div>
            <div class="dashboard-hero-stats">
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
@endsection
