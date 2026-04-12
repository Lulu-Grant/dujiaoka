@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @if(!empty($maintenanceNote))
        <section class="panel">
            <div class="panel-body">
                <div class="notice success" style="margin-bottom: 0;">{{ $maintenanceNote }}</div>
            </div>
        </section>
    @endif

    @if(!empty($summaryCards))
        <section class="panel">
            <div class="panel-body">
                <div class="detail-grid">
                    @foreach($summaryCards as $card)
                        <div class="detail-item" style="min-height: 108px;">
                            <div class="detail-label">{{ $card['label'] }}</div>
                            <div class="detail-value">{!! $card['value'] !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if(!empty($quickTips))
        <section class="panel">
            <div class="panel-body">
                <div class="notice success">
                    @foreach($quickTips as $tip)
                        <div>{{ $tip }}</div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('admin-shell.partials.filter-panel', $filterPanel)
    @include('admin-shell.partials.data-table', $table)
@endsection
