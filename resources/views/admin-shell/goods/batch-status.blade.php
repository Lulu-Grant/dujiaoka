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
                    <div class="detail-label">推荐用途</div>
                    <div class="detail-value">活动上下架、灰度启用、临时停售</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">风险边界</div>
                    <div class="detail-value">仅更新启用状态，不改库存、价格和关联数据</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页刻意做成低风险批量动作。我们先把最常见的“上架 / 下架”收进后台壳，再慢慢把更复杂的批量操作接进来。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>商品 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、空格或逗号分隔。适合从概览页、工单或巡检清单里直接粘贴一串商品 ID。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;96001&#10;96002&#10;96003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>目标状态</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">先统一切换启用状态，后续再补批量排序、库存和更多运营动作。</small>
                        <select name="is_open" required>
                            <option value="1" @if((string) old('is_open', $defaults['is_open']) === '1') selected @endif>批量启用</option>
                            <option value="0" @if((string) old('is_open', $defaults['is_open']) === '0') selected @endif>批量停用</option>
                        </select>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/goods') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($context['items']))
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">匹配预览</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会影响到这些商品</h2>
                <p class="page-description">先看一眼商品名称、类型和当前状态，确认这批 ID 没有粘错，再执行状态切换。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['items'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $item['id'] }} · {{ $item['type'] }}</div>
                            <div class="detail-value">{{ $item['name'] }}</div>
                            <div class="meta" style="margin-top: 8px;">当前状态：{{ $item['status'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
