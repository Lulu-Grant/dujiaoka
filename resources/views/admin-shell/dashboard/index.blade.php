@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    <style>
        .dashboard-frame {
            display: grid;
            gap: 16px;
        }
        .dashboard-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .dashboard-card,
        .dashboard-panel {
            background: #ffffff;
            border: 1px solid var(--shell-line);
            border-radius: 10px;
            box-shadow: var(--shell-shadow);
        }
        .dashboard-card {
            padding: 16px;
            border-left: 4px solid var(--card-accent);
        }
        .dashboard-card span,
        .dashboard-row span,
        .dashboard-brief span {
            color: var(--shell-muted);
            font-size: 13px;
            line-height: 1.55;
        }
        .dashboard-card strong {
            display: block;
            margin-top: 8px;
            color: var(--shell-ink);
            font-size: 28px;
            line-height: 1;
            font-weight: 800;
        }
        .dashboard-panel {
            padding: 16px;
        }
        .dashboard-panel h2 {
            margin: 0 0 12px;
            color: var(--shell-ink);
            font-size: 17px;
            line-height: 1.3;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
        .dashboard-list {
            display: grid;
            gap: 10px;
        }
        .dashboard-row,
        .dashboard-brief {
            display: grid;
            gap: 4px;
            padding: 10px 0;
            border-bottom: 1px solid var(--shell-line);
        }
        .dashboard-row:last-child,
        .dashboard-brief:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .dashboard-row strong,
        .dashboard-brief strong {
            color: var(--shell-ink);
            font-size: 18px;
        }
        .dashboard-segments {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .dashboard-segment-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--shell-line);
        }
        .dashboard-segment-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .dashboard-segment-row span {
            color: var(--shell-muted);
        }
        .dashboard-segment-row strong {
            color: var(--shell-ink);
        }
        .dashboard-health-badge {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 8px;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .dashboard-health-badge::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
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
        .accent-lime { --card-accent: #2563eb; }
        .accent-amber { --card-accent: #f97316; }
        .accent-teal { --card-accent: #0ea5e9; }
        .accent-rose { --card-accent: #dc2626; }
        @media (max-width: 1180px) {
            .dashboard-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .dashboard-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="dashboard-frame">
        <section class="dashboard-summary">
            <article class="dashboard-card accent-lime">
                <span>今日成功率</span>
                <strong>{{ $hero['success_rate'] }}%</strong>
            </article>
            <article class="dashboard-card accent-teal">
                <span>今日订单数</span>
                <strong>{{ $hero['order_count'] }}</strong>
            </article>
            <article class="dashboard-card accent-amber">
                <span>今日销售额</span>
                <strong>{{ $hero['sales_total'] }}</strong>
            </article>
            <article class="dashboard-card accent-rose">
                <span>已完成订单</span>
                <strong>{{ $hero['completed_count'] }}</strong>
            </article>
        </section>

        <section class="dashboard-grid">
            <div class="dashboard-panel">
                <h2>系统健康状态</h2>
                <div class="dashboard-list">
                    <div class="dashboard-row">
                        <span>健康评分</span>
                        <strong>{{ $health['score'] }}/100</strong>
                    </div>
                    <div class="dashboard-row">
                        <span>状态判定</span>
                        <div class="dashboard-health-badge {{ $health['tone'] }}">{{ $health['label'] }}</div>
                    </div>
                    <div class="dashboard-row">
                        <span>说明</span>
                        <span>{{ $health['note'] }}</span>
                    </div>
                </div>
            </div>
            <div class="dashboard-panel">
                <h2>重点信号</h2>
                <div class="dashboard-list">
                    @foreach($operations as $operation)
                        <div class="dashboard-row">
                            <span>{{ $operation['label'] }}</span>
                            <strong>{{ $operation['value'] }}</strong>
                            <span>{{ $operation['note'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="dashboard-panel">
                <h2>本日操作建议</h2>
                <div class="dashboard-list">
                    @foreach($operator_brief as $brief)
                        <div class="dashboard-brief">
                            <strong>{{ $brief['title'] }}</strong>
                            <span>{{ $brief['description'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="dashboard-summary">
            @foreach($cards as $card)
                <article class="dashboard-card accent-{{ $card['accent'] }}">
                    <span>{{ $card['title'] }}</span>
                    <strong>{{ $card['value'] }}</strong>
                    <span>{{ $card['description'] }}</span>
                </article>
            @endforeach
        </section>

        <section class="dashboard-segments">
            @foreach($segments as $segment)
                <div class="dashboard-panel">
                    <h2>{{ $segment['title'] }}</h2>
                    @foreach($segment['items'] as $item)
                        <div class="dashboard-segment-row">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </section>
    </div>
@endsection
