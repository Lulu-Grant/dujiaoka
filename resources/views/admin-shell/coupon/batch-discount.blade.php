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
                    <div class="detail-label">待处理优惠码数</div>
                    <div class="detail-value">{{ $context['matchedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">输入 ID 数</div>
                    <div class="detail-value">{{ $context['requestedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">未匹配 ID 数</div>
                    <div class="detail-value">{{ $context['missingCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">风险边界</div>
                    <div class="detail-value">仅更新折扣，不改使用状态、次数、启用状态和关联商品</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页只收“批量调折扣”一个动作。我们先把最常见的活动价修正收进后台壳，再逐步把更多优惠码维护动作补齐。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>优惠码 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、空格、逗号或中文逗号分隔。适合从活动名单、运营表格或测试清单里直接粘贴一串优惠码 ID。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;95001&#10;95002&#10;95003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>目标折扣金额</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">只统一修改折扣值，不会影响可用次数、启用状态和使用状态。</small>
                        <input type="number" name="discount" min="0" step="0.01" value="{{ old('discount', $defaults['discount']) }}" required>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/coupon') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    @if(($context['requestedCount'] ?? 0) > 0)
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">匹配预览</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会影响到这些优惠码</h2>
                <p class="page-description">先看一眼优惠码、当前折扣、使用状态和可用次数，确认这批 ID 没有粘错，再执行统一调价。</p>

                @if(!empty($context['items']))
                    <div class="detail-grid" style="margin-top: 20px;">
                        @foreach($context['items'] as $item)
                            <div class="detail-item">
                                <div class="detail-label">#{{ $item['id'] }} · 当前折扣 {{ $item['discount'] }}</div>
                                <div class="detail-value">{{ $item['code'] }}</div>
                                <div class="meta" style="margin-top: 8px;">当前使用状态：{{ $item['usage'] }}</div>
                                <div class="meta" style="margin-top: 4px;">当前启用状态：{{ $item['status'] }} · 可用次数：{{ $item['ret'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="notice muted" style="margin-top: 18px;">
                        这次输入的优惠码 ID 里没有匹配到有效记录，提交后不会更新任何优惠码的折扣。
                    </div>
                @endif
            </div>
        </section>
    @endif
@endsection
