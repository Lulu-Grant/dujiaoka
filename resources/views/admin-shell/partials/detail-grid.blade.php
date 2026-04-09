<section class="panel">
    <div class="panel-body detail-grid">
        @foreach($items as $item)
            <div class="detail-item" @if(!empty($item['style'])) style="{{ $item['style'] }}" @endif>
                <div class="detail-label">{{ $item['label'] }}</div>
                <div class="detail-value" @if(!empty($item['value_style'])) style="{{ $item['value_style'] }}" @endif>
                    {!! $item['value'] !!}
                </div>
            </div>
        @endforeach
    </div>
</section>
