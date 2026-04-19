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
                    <div class="detail-label">匹配商品数</div>
                    <div class="detail-value">{{ $context['matchedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">缺失商品数</div>
                    <div class="detail-value">{{ count($context['missingIds']) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">风险边界</div>
                    <div class="detail-value">仅更新排序，不改价格、库存、分类、商品类型和启用状态</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页只做一件事：批量设置排序。先输入一串商品 ID 预览匹配结果，再统一调整目标排序值。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>商品 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、逗号或空格分隔。建议先从商品概览页筛出一组商品，再回来执行批量维护。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;96001&#10;96002&#10;96003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>目标排序</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">这一步只会更新排序字段，适合活动编排和列表顺序整理。</small>
                        <input type="number" min="0" name="ord" value="{{ old('ord', $defaults['ord']) }}" required>
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
                <div class="page-kicker">缺失商品 ID</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这些 ID 没有匹配到商品</h2>
                <p class="page-description">这些 ID 会被忽略，不影响已匹配商品的批量排序更新。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['missingIds'] as $missingId)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $missingId }}</div>
                            <div class="detail-value">未找到对应商品</div>
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
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会更新这些商品的排序</h2>
                <p class="page-description">先确认商品名称、类型、当前状态和当前排序，再执行批量更新，避免粘错 ID。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['items'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $item['id'] }} · {{ $item['status'] }}</div>
                            <div class="detail-value">{{ $item['name'] }}</div>
                            <div class="meta" style="margin-top: 8px;">类型：{{ $item['type'] }}</div>
                            <div class="meta" style="margin-top: 6px;">当前排序：{{ $item['ord'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
