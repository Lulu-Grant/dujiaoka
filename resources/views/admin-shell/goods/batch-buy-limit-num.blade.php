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
                    <div class="detail-label">待处理商品数</div>
                    <div class="detail-value">{{ $context['matchedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">输入 ID 数</div>
                    <div class="detail-value">{{ $context['requestedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">缺失 ID 数</div>
                    <div class="detail-value">{{ count($context['missingIds'] ?? []) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">风险边界</div>
                    <div class="detail-value">仅更新限购数量，不改价格、库存、分类和启用状态</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf
                <input type="hidden" name="mode" value="batch-buy-limit-num">

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页专门给运营做限购收口。批量更新只影响 `buy_limit_num`，不会动商品价格、库存、分类或上架状态。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>商品 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、空格、逗号或中文逗号混输。适合从活动清单里直接粘贴一串商品 ID。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;96001&#10;96002&#10;96003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>限购数量</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">设为 0 表示不限制单次购买数量。</small>
                        <input type="number" name="buy_limit_num" min="0" value="{{ old('buy_limit_num', $defaults['buy_limit_num']) }}" required>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/goods') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($context['missingIds']))
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">缺失 ID</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">有些商品 ID 没有找到</h2>
                <p class="page-description">这些 ID 不会被更新，先确认是不是少粘了前缀或者录入时写错了。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['missingIds'] as $missingId)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $missingId }}</div>
                            <div class="detail-value">未找到商品记录</div>
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
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会影响到这些商品</h2>
                <p class="page-description">先看一眼商品名称、类型和当前限购数量，确认这批 ID 没有粘错，再执行更新。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['items'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $item['id'] }} · {{ $item['type'] }}</div>
                            <div class="detail-value">{{ $item['name'] }}</div>
                            <div class="meta" style="margin-top: 8px;">当前状态：{{ $item['status'] }}</div>
                            <div class="meta" style="margin-top: 4px;">当前限购：{{ $item['buy_limit_num'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
