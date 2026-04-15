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
                    <div class="detail-value">仅更新循环使用标记，不改卡密内容和销售状态</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页只做一件事：批量设置卡密是否循环使用。先输入一串卡密 ID 预览匹配结果，再统一切换标记。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>卡密 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、逗号或空格分隔。适合从卡密概览页筛出一组卡密后统一调整循环策略。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;95001&#10;95002&#10;95003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>循环使用</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">这一步只调整循环使用标记，不会影响卡密内容和销售状态。</small>
                        <select name="is_loop" required>
                            <option value="1" @if((string) old('is_loop', $defaults['is_loop']) === '1') selected @endif>启用循环使用</option>
                            <option value="0" @if((string) old('is_loop', $defaults['is_loop']) === '0') selected @endif>关闭循环使用</option>
                        </select>
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
                <p class="page-description">这些 ID 会被忽略，不影响已匹配卡密的循环使用设置。</p>

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
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会更新这些卡密的循环使用标记</h2>
                <p class="page-description">先确认商品归属、销售状态和当前循环标记，再执行批量更新。</p>

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
