@extends('admin-shell.layout', ['title' => '邮件模板管理 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '邮件模板管理',
        'description' => '这是第二张后台壳样板页。当前列表、筛选和详情都通过普通 Laravel 控制器与 Blade 组合，不再依赖 Dcat Grid/Show。',
        'meta' => '共 '.$templates->total().' 条模板',
    ])

    @include('admin-shell.partials.filter-panel', [
        'fields' => [
            ['label' => 'ID', 'name' => 'id', 'type' => 'number', 'value' => $filters['id']],
            ['label' => '邮件标题', 'name' => 'tpl_name', 'value' => $filters['tpl_name']],
            ['label' => '邮件标识', 'name' => 'tpl_token', 'value' => $filters['tpl_token']],
        ],
        'resetUrl' => admin_url('v2/emailtpl'),
    ])

    @include('admin-shell.partials.data-table', $table)
@endsection
