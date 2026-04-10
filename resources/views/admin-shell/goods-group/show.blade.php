@extends('admin-shell.layout', ['title' => '商品分类详情 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '商品分类详情',
        'description' => '这是商品分类页的详情样板。后续真正替换后台壳时，可以直接照着这组字段合同迁移。',
        'actions' => [
            ['label' => '返回列表', 'href' => admin_url('v2/goods-group'.($scope ? '?scope='.$scope : ''))],
        ],
    ])

    @include('admin-shell.partials.detail-grid', ['items' => $items])
@endsection
