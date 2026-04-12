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

    <section class="panel">
        <div class="panel-body">
            <div class="coupon-clipboard" style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: center;">
                <div>
                    <div class="page-kicker">当前优惠码</div>
                    <div style="font-size: 30px; font-weight: 900; color: var(--shell-ink); letter-spacing: 1px; margin-top: 6px;">{{ $couponCode }}</div>
                    @if(!empty($couponCopyHint))
                        <div class="meta" style="margin-top: 8px;">{{ $couponCopyHint }}</div>
                    @endif
                </div>
                <div class="page-actions">
                    <button class="button" type="button" data-copy-coupon="{{ e($couponCode) }}">{{ $couponCopyLabel }}</button>
                    @if(!empty($couponEditUrl))
                        <a class="button secondary" href="{{ $couponEditUrl }}">编辑优惠码</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

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

    @include('admin-shell.partials.detail-grid', ['items' => $items])

    <script>
        (function () {
            var button = document.querySelector('[data-copy-coupon]');
            if (!button) {
                return;
            }

            button.addEventListener('click', function () {
                var coupon = button.getAttribute('data-copy-coupon') || '';

                if (!coupon) {
                    return;
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(coupon);
                } else {
                    var textarea = document.createElement('textarea');
                    textarea.value = coupon;
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                }

                button.textContent = '已复制';
                setTimeout(function () {
                    button.textContent = '{{ $couponCopyLabel }}';
                }, 1600);
            });
        })();
    </script>
@endsection
