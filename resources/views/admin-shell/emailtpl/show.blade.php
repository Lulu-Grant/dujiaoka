@extends('admin-shell.layout', ['title' => '邮件模板详情 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '邮件模板详情',
        'description' => '这张详情页用于固定邮件模板的字段合同，后续新后台壳可以直接复用。',
        'actions' => [
            ['label' => '返回列表', 'href' => admin_url('v2/emailtpl')],
        ],
    ])

    @include('admin-shell.partials.detail-grid', ['items' => $items])
@endsection
