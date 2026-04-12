<header class="page-header">
    <div class="page-header__copy">
        <div class="page-kicker">{{ $kicker ?? 'Admin Shell' }}</div>
        <h1 class="page-title">{{ $title }}</h1>
        @if(!empty($description))
            <p class="page-description">{{ $description }}</p>
        @endif
    </div>

    @if(!empty($meta) || !empty($actions))
        <div class="page-header__aside">
            @if(!empty($meta))
                <div class="page-meta">{{ $meta }}</div>
            @endif

            @if(!empty($actions))
                <div class="page-actions">
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
