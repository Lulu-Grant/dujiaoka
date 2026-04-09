@extends('admin-shell.layout', ['title' => '商品分类详情 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '商品分类详情',
        'description' => '这是商品分类页的详情样板。后续真正替换后台壳时，可以直接照着这组字段合同迁移。',
        'actions' => [
            ['label' => '返回列表', 'href' => admin_url('v2/goods-group'.($scope ? '?scope='.$scope : ''))],
        ],
    ])

    @include('admin-shell.partials.detail-grid', [
        'items' => [
            ['label' => 'ID', 'value' => $group->id],
            ['label' => '分类名称', 'value' => $group->gp_name],
            ['label' => '状态', 'value' => strip_tags($statusPresenter->openStatusLabel($group->is_open))],
            ['label' => '排序', 'value' => $group->ord],
            ['label' => '商品数', 'value' => $group->goods_count],
            ['label' => '创建时间', 'value' => $group->created_at],
            ['label' => '更新时间', 'value' => $group->updated_at],
            ['label' => '删除状态', 'value' => $group->deleted_at ? '已删除' : '正常'],
        ],
    ])
@endsection
