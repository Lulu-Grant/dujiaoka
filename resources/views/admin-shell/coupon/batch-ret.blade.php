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
                    <div class="detail-label">匹配优惠码数</div>
                    <div class="detail-value">{{ $context['matchedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">未匹配数量</div>
                    <div class="detail-value">{{ $context['missingCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">风险边界</div>
                    <div class="detail-value">只更新可用次数，不改折扣、启停和关联商品</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页专门给运营统一调整优惠码可用次数。批量更新只影响 `ret`，不会改折扣金额、启用状态和商品关联。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>优惠码 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、空格、逗号或中文逗号混输。适合从工单或列表里一次粘贴一批优惠码 ID。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;95001&#10;95002&#10;95003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>目标可用次数</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">填 `0` 可作为停发前的人工兜底，填正整数可统一补量或限次。</small>
                        <input type="number" min="0" name="ret" value="{{ old('ret', $defaults['ret']) }}" required>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/coupon') }}">返回概览</a>
                    <a class="button secondary" href="{{ admin_url('v2/coupon/batch-status') }}">切到批量启停</a>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($context['items']))
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">匹配预览</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会影响到这些优惠码</h2>
                <p class="page-description">先看一眼优惠码、折扣和当前次数，确认这批 ID 没有粘错，再统一调整可用次数。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['items'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $item['id'] }} · {{ $item['usage'] }}</div>
                            <div class="detail-value">{{ $item['code'] }}</div>
                            <div class="meta" style="margin-top: 8px;">当前状态：{{ $item['status'] }}</div>
                            <div class="meta" style="margin-top: 4px;">折扣：{{ $item['discount'] }} · 当前可用次数：{{ $item['ret'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
