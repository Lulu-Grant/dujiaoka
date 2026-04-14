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
                    <div class="detail-label">匹配订单数</div>
                    <div class="detail-value">{{ $context['matchedCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">缺失订单数</div>
                    <div class="detail-value">{{ $context['missingCount'] }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">风险边界</div>
                    <div class="detail-value">仅更新订单状态，不改支付、履约和通知链</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页只做一件事：批量更新订单状态。先输入一串订单 ID 预览匹配结果，再统一切换到目标状态。
                </div>

                <div class="filters" style="align-items: stretch;">
                    <label style="grid-column: 1 / -1;">
                        <span>订单 ID 列表</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">支持换行、逗号或空格分隔。建议先从订单概览页筛出一组订单，再回来执行批量维护。</small>
                        <textarea name="ids_text" rows="8" placeholder="例如：&#10;98001&#10;98002&#10;98003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    </label>
                    <label>
                        <span>目标状态</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">这一步只会更新订单状态字段，不会触发支付完成、发货或通知。</small>
                        <select name="status" required>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('status', $defaults['status']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/order') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($context['missingIds']))
        <section class="panel">
            <div class="panel-body">
                <div class="page-kicker">缺失订单 ID</div>
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这些 ID 没有匹配到订单</h2>
                <p class="page-description">这些 ID 会被忽略，不影响已匹配订单的批量状态更新。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['missingIds'] as $missingId)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $missingId }}</div>
                            <div class="detail-value">未找到对应订单</div>
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
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会更新这些订单的状态</h2>
                <p class="page-description">先确认订单号、标题、当前状态和金额，再执行批量更新，避免粘错 ID。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['items'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $item['id'] }} · {{ $item['status'] }}</div>
                            <div class="detail-value">{{ $item['order_sn'] }}</div>
                            <div class="meta" style="margin-top: 8px;">{{ $item['title'] }}</div>
                            <div class="meta" style="margin-top: 6px;">邮箱：{{ $item['email'] }}</div>
                            <div class="meta" style="margin-top: 6px;">商品：{{ $item['goods'] }}</div>
                            <div class="meta" style="margin-top: 6px;">支付：{{ $item['pay'] }}</div>
                            <div class="meta" style="margin-top: 6px;">实付金额：{{ $item['actual_price'] }}</div>
                            <div class="meta" style="margin-top: 6px;">更新时间：{{ $item['updated_at'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
