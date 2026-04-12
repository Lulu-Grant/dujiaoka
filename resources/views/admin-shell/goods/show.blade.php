@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    <section class="panel">
        <div class="panel-body">
            <div class="detail-grid">
                @foreach($summaryCards as $card)
                    <div class="detail-item" style="min-height: 118px;">
                        <div class="detail-label">{{ $card['label'] }}</div>
                        <div class="detail-value">{!! $card['value'] !!}</div>
                        @if(!empty($card['note']))
                            <div class="meta" style="margin-top: 8px;">{{ $card['note'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @foreach($sections as $section)
        <section class="panel">
            <div class="panel-body">
                <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
                    <div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--shell-ink);">{{ $section['title'] }}</div>
                        <p class="meta" style="margin-top: 6px;">{{ $section['description'] }}</p>
                    </div>
                    @if(!empty($section['note']))
                        <div class="page-meta" style="max-width: 340px;">{{ $section['note'] }}</div>
                    @endif
                </div>

                <div class="detail-grid">
                    @foreach($section['items'] as $item)
                        <div class="detail-item" @if(!empty($item['span'])) style="grid-column: 1 / -1;" @endif>
                            <div class="detail-label">{{ $item['label'] }}</div>
                            <div class="detail-value" @if(!empty($item['value_style'])) style="{{ $item['value_style'] }}" @endif>{!! $item['value'] !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
@endsection
