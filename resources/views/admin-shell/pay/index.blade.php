@extends('admin-shell.layout', ['title' => '支付通道管理 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '支付通道管理',
        'description' => '这是第一批后台迁移的第三张样板页。支付通道的生命周期、支付方式、支付场景都直接复用现有 presenter 与模型映射。',
        'meta' => '共 '.$pays->total().' 条通道',
    ])

    @include('admin-shell.partials.filter-panel', [
        'fields' => [
            ['label' => 'ID', 'name' => 'id', 'type' => 'number', 'value' => $filters['id']],
            ['label' => '支付标识', 'name' => 'pay_check', 'value' => $filters['pay_check']],
            ['label' => '支付名称', 'name' => 'pay_name', 'value' => $filters['pay_name']],
            [
                'label' => '范围',
                'name' => 'scope',
                'type' => 'select',
                'value' => $filters['scope'],
                'options' => ['' => '全部', 'trashed' => '回收站'],
            ],
        ],
        'resetUrl' => admin_url('v2/pay'),
    ])

    @include('admin-shell.partials.data-table', $table)
@endsection
