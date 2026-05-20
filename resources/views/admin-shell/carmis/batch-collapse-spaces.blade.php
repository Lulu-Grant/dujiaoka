@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @if(session('status'))
        <div class="panel">
            <div class="panel-body">
                <div class="notice success">{{ session('status') }}</div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="panel">
            <div class="panel-body">
                <div class="notice error">{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <section class="panel">
        <div class="panel-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">输入 ID 数</div>
                    <div class="detail-value">{{ $context['requestedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">匹配卡密数</div>
                    <div class="detail-value">{{ $context['matchedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">缺失卡密数</div>
                    <div class="detail-value">{{ $context['missingCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">风险边界</div>
                    <div class="detail-value">仅压缩卡密内容连续空格，不改销售状态、循环使用和商品归属</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页只做一件事：批量压缩卡密内容里的连续空格。先输入一串卡密 ID 预览匹配结果，再统一清理文本格式。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>卡密 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、逗号或空格分隔。保存时会自动去重，只压缩命中卡密内容中的连续空格。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;95001&#10;95002&#10;95003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/carmis') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($context['missingIds']))
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">缺失卡密 ID</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这些 ID 没有匹配到卡密</h2>
                <p class="page-description">这些 ID 会被忽略，不影响已匹配卡密的连续空格压缩。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['missingIds'] as $missingId)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $missingId }}</div>
                            <div class="detail-value">未找到对应卡密</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if(!empty($context['items']))
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">匹配预览</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会压缩这些卡密的内容连续空格</h2>
                <p class="page-description">先确认商品归属、销售状态和当前卡密内容，再执行批量清理。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['items'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $item['id'] }} · {{ $item['status'] }}</div>
                            <div class="detail-value">{{ $item['goods'] }}</div>
                            <div class="meta" style="margin-top: 8px;">当前循环使用：{{ $item['is_loop'] }}</div>
                            <div class="meta" style="margin-top: 6px;">卡密：{{ $item['carmi'] }}</div>
                            <div class="meta" style="margin-top: 6px;">更新时间：{{ $item['updated_at'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
