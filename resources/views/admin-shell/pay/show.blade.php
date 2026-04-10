@extends('admin-shell.layout', ['title' => '支付通道详情 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '支付通道详情',
        'description' => '这张详情页固定了支付通道的展示合同，后续迁移编辑页时可以直接在这套壳上扩展。',
        'actions' => [
            ['label' => '返回列表', 'href' => admin_url('v2/pay'.($scope ? '?scope='.$scope : ''))],
        ],
    ])

    @include('admin-shell.partials.detail-grid', ['items' => $items])
@endsection
