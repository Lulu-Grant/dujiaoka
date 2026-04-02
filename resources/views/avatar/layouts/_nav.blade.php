<div class="avatar-nav">
    <div class="container avatar-nav-inner">
        <a href="/" class="avatar-brand">
            @if(dujiaoka_config_get('img_logo'))
                <img src="{{ picture_ulr(dujiaoka_config_get('img_logo')) }}" alt="{{ dujiaoka_config_get('text_logo') }}">
            @else
                <span class="avatar-brand-mark">A</span>
            @endif
            <div class="avatar-brand-text">
                <div class="avatar-brand-name">{{ dujiaoka_config_get('text_logo', 'Avatar Store') }}</div>
                <div class="avatar-brand-tag">Digital checkout storefront</div>
            </div>
        </a>
        <div class="avatar-nav-actions">
            <a class="avatar-nav-link" href="{{ url('order-search') }}">查询订单</a>
        </div>
    </div>
</div>
