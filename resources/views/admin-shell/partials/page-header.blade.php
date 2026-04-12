<header class="page-header">
    <div>
        <div class="page-kicker">{{ $kicker ?? 'Admin Shell Sample' }}</div>
        <h1 class="page-title">{{ $title }}</h1>
        @if(!empty($description))
            <p class="page-description">{{ $description }}</p>
        @endif
    </div>

    @if(!empty($meta) || !empty($actions))
        <div>
            @if(!empty($meta))
                <div class="meta">{{ $meta }}</div>
            @endif

            @if(!empty($actions))
                <div class="button-row" style="{{ !empty($meta) ? 'margin-top: 10px;' : '' }}">
                    @foreach($actions as $action)
                        <a class="button {{ $action['variant'] ?? 'secondary' }}" href="{{ $action['href'] }}">
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</header>
