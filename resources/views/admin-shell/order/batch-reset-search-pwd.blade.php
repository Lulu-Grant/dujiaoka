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
                    <div class="detail-value">只重置查询密码，不改订单状态、支付和履约字段</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice info" style="margin-bottom: 18px;">
                    这张页只做一件事：批量重置订单查询密码。先输入一串订单 ID 预览匹配结果，再提交执行。
                </div>

                <label>
                    <span>订单 ID 列表</span>
                    <small style="display:block; margin-top:6px; color:#66756b;">支持换行、逗号或空格分隔。建议先从订单概览页复制一组 ID，再回来执行批量重置。</small>
                    <textarea name="ids_text" rows="8" placeholder="例如：&#10;97001&#10;97002&#10;97003">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                </label>

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
                <p class="page-description">这些 ID 会被忽略，不影响已匹配订单的查询密码重置。</p>

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
                <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">这次会重置这些订单的查询密码</h2>
                <p class="page-description">先确认订单号、标题、状态和当前查询密码，再执行批量重置，避免粘错 ID。</p>

                <div class="detail-grid" style="margin-top: 20px;">
                    @foreach($context['items'] as $item)
                        <div class="detail-item">
                            <div class="detail-label">#{{ $item['id'] }} · {{ $item['status'] }}</div>
                            <div class="detail-value">{{ $item['order_sn'] }}</div>
                            <div class="meta" style="margin-top: 8px;">{{ $item['title'] }}</div>
                            <div class="meta" style="margin-top: 6px;">邮箱：{{ $item['email'] }}</div>
                            <div class="meta" style="margin-top: 6px;">商品：{{ $item['goods'] }}</div>
                            <div class="meta" style="margin-top: 6px;">支付：{{ $item['pay'] }}</div>
                            <div class="meta" style="margin-top: 6px;">当前查询密码：{{ $item['search_pwd'] }}</div>
                            <div class="meta" style="margin-top: 6px;">更新时间：{{ $item['updated_at'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
