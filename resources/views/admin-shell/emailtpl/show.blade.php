@extends('admin-shell.layout', ['title' => '邮件模板详情 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '邮件模板详情',
        'description' => '这张详情页用于固定邮件模板的字段合同，后续新后台壳可以直接复用。',
        'actions' => [
            ['label' => '返回列表', 'href' => admin_url('v2/emailtpl')],
        ],
    ])

    @include('admin-shell.partials.detail-grid', [
        'items' => [
            ['label' => 'ID', 'value' => $template->id],
            ['label' => '邮件标题', 'value' => $template->tpl_name],
            ['label' => '邮件标识', 'value' => $template->tpl_token],
            [
                'label' => '邮件内容',
                'value' => e($template->tpl_content),
                'style' => 'grid-column: 1 / -1;',
                'value_style' => 'white-space: pre-wrap; font-size: 14px; font-weight: 500;',
            ],
            ['label' => '创建时间', 'value' => $template->created_at],
            ['label' => '更新时间', 'value' => $template->updated_at],
        ],
    ])
@endsection
