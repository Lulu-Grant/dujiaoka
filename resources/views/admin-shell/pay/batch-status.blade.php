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
                    <div class="detail-label">待处理通道数</div>
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
                    <div class="detail-value">仅更新启用状态，不改商户密钥和回调路由</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页刻意做成低风险批量动作。我们先把最常见的“启用 / 停用”收进后台壳，再慢慢把更复杂的批量操作接进来。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>支付通道 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、空格、逗号或中文逗号分隔。适合从概览页、工单或巡检清单里直接粘贴一串支付通道 ID。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;93001&#10;93002&#10;93003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>目标状态</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">先统一切换启用状态，后续再补批量复制、归档和更多运营动作。</small>
                        <select name="is_open" required>
                            <option value="1" @if((string) old('is_open', $defaults['is_open']) === '1') selected @endif>批量启用</option>
                            <option value="0" @if((string) old('is_open', $defaults['is_open']) === '0') selected @endif>批量停用</option>
                        </select>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/pay') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    @if(($context['requestedCount'] ?? 0) > 0)
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">匹配预览</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会影响到这些支付通道</h2>
                <p class="page-description">先看一眼支付名称、生命周期和当前状态，确认这批 ID 没有粘错，再执行状态切换。</p>

                @if(!empty($context['items']))
                    <div class="detail-grid" style="margin-top: 20px;">
                        @foreach($context['items'] as $item)
                            <div class="detail-item">
                                <div class="detail-label">#{{ $item['id'] }} · {{ $item['client'] }} / {{ $item['method'] }}</div>
                                <div class="detail-value">{{ $item['name'] }}</div>
                                <div class="meta" style="margin-top: 8px;">
                                    标识：{{ $item['check'] }} · 生命周期：{{ $item['lifecycle'] }} · 当前状态：{{ $item['status'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="notice muted" style="margin-top: 18px;">
                        这次输入的支付通道 ID 里没有匹配到有效记录，提交后不会更新任何通道状态。
                    </div>
                @endif

                @if(!empty($context['missingCount']))
                    <div class="notice warning" style="margin-top: 18px;">
                        以下 ID 没有找到对应支付通道：{{ implode(', ', $context['missingIds']) }}
                    </div>
                @endif
            </div>
        </section>
    @endif
@endsection
