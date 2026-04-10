@extends('admin-shell.layout', ['title' => '商品分类管理 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '商品分类管理',
        'description' => '这是第一批后台迁移样板页。当前使用普通 Laravel 控制器、服务和 Blade 渲染，不再依赖 Dcat Grid。',
        'meta' => '共 '.$groups->total().' 条记录',
    ])

    @include('admin-shell.partials.filter-panel', [
        'fields' => [
            ['label' => 'ID', 'name' => 'id', 'type' => 'number', 'value' => $filters['id']],
            [
                'label' => '范围',
                'name' => 'scope',
                'type' => 'select',
                'value' => $filters['scope'],
                'options' => ['' => '全部', 'trashed' => '回收站'],
            ],
        ],
        'resetUrl' => admin_url('v2/goods-group'),
    ])

    @include('admin-shell.partials.data-table', $table)
@endsection
