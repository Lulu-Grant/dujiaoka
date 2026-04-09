@extends('admin-shell.layout', ['title' => '支付通道详情 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '支付通道详情',
        'description' => '这张详情页固定了支付通道的展示合同，后续迁移编辑页时可以直接在这套壳上扩展。',
        'actions' => [
            ['label' => '返回列表', 'href' => admin_url('v2/pay'.($scope ? '?scope='.$scope : ''))],
        ],
    ])

    @include('admin-shell.partials.detail-grid', [
        'items' => [
            ['label' => 'ID', 'value' => $pay->id],
            ['label' => '支付名称', 'value' => $pay->pay_name],
            ['label' => '支付标识', 'value' => $pay->pay_check],
            ['label' => '生命周期', 'value' => $presenter->lifecycleLabel($pay->lifecycle)],
            ['label' => '支付场景', 'value' => $presenter->clientLabel($pay->pay_client)],
            ['label' => '支付方式', 'value' => $presenter->methodLabel($pay->pay_method)],
            ['label' => '启用状态', 'value' => strip_tags($presenter->openStatusLabel($pay->is_open))],
            ['label' => '支付路由', 'value' => $pay->pay_handleroute],
            ['label' => '商户 ID', 'value' => $pay->merchant_id],
            ['label' => '商户 KEY', 'value' => $pay->merchant_key],
            ['label' => '商户密钥', 'value' => $pay->merchant_pem],
            ['label' => '创建时间', 'value' => $pay->created_at],
            ['label' => '更新时间', 'value' => $pay->updated_at],
        ],
    ])
@endsection
