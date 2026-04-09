@extends('admin-shell.layout', ['title' => '支付通道详情 - 后台壳样板'])

@section('content')
    <header class="page-header">
        <div>
            <div class="page-kicker">Admin Shell Sample</div>
            <h1 class="page-title">支付通道详情</h1>
            <p class="page-description">这张详情页固定了支付通道的展示合同，后续迁移编辑页时可以直接在这套壳上扩展。</p>
        </div>
        <div class="button-row">
            <a class="button secondary" href="{{ admin_url('v2/pay'.($scope ? '?scope='.$scope : '')) }}">返回列表</a>
        </div>
    </header>

    <section class="panel">
        <div class="panel-body detail-grid">
            <div class="detail-item">
                <div class="detail-label">ID</div>
                <div class="detail-value">{{ $pay->id }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">支付名称</div>
                <div class="detail-value">{{ $pay->pay_name }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">支付标识</div>
                <div class="detail-value">{{ $pay->pay_check }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">生命周期</div>
                <div class="detail-value">{{ $presenter->lifecycleLabel($pay->lifecycle) }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">支付场景</div>
                <div class="detail-value">{{ $presenter->clientLabel($pay->pay_client) }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">支付方式</div>
                <div class="detail-value">{{ $presenter->methodLabel($pay->pay_method) }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">启用状态</div>
                <div class="detail-value">{{ strip_tags($presenter->openStatusLabel($pay->is_open)) }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">支付路由</div>
                <div class="detail-value">{{ $pay->pay_handleroute }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">商户 ID</div>
                <div class="detail-value">{{ $pay->merchant_id }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">商户 KEY</div>
                <div class="detail-value">{{ $pay->merchant_key }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">商户密钥</div>
                <div class="detail-value">{{ $pay->merchant_pem }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">创建时间</div>
                <div class="detail-value">{{ $pay->created_at }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">更新时间</div>
                <div class="detail-value">{{ $pay->updated_at }}</div>
            </div>
        </div>
    </section>
@endsection
