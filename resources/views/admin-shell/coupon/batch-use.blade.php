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
                    <div class="detail-value">仅更新使用状态，不改折扣、次数、启用状态和关联商品</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页刻意只收一个低风险动作。我们先把最常见的“未使用 / 已使用”纠偏收进后台壳，再逐步扩更多优惠码维护能力。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>优惠码 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、空格、逗号或中文逗号分隔。适合从活动清单、售后工单或测试记录里直接粘贴一串优惠码 ID。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;95001&#10;95002&#10;95003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>目标使用状态</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">这一步只修正优惠码是否已使用的标记，不会影响可用次数和启用状态。</small>
                        <select name="is_use" required>
                            @foreach($usageOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('is_use', $defaults['is_use']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
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
                <p class="page-description">先看一眼优惠码、折扣、当前使用状态和可用次数，确认这批 ID 没有粘错，再执行状态切换。</p>

                @if(!empty($context['items']))
                    <div class="detail-grid" style="margin-top: 20px;">
                        @foreach($context['items'] as $item)
                            <div class="detail-item">
                                <div class="detail-label">#{{ $item['id'] }} · 折扣 {{ $item['discount'] }}</div>
                                <div class="detail-value">{{ $item['code'] }}</div>
                                <div class="meta" style="margin-top: 8px;">当前使用状态：{{ $item['usage'] }}</div>
                                <div class="meta" style="margin-top: 4px;">当前启用状态：{{ $item['status'] }} · 可用次数：{{ $item['ret'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="notice muted" style="margin-top: 18px;">
                        这次输入的优惠码 ID 里没有匹配到有效记录，提交后不会更新任何优惠码的使用状态。
                    </div>
                @endif
            </div>
        </section>
    @endif
@endsection
