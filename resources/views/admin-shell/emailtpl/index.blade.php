@extends('admin-shell.layout', ['title' => '邮件模板管理 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @include('admin-shell.partials.filter-panel', $filterPanel)

    @include('admin-shell.partials.data-table', $table)
@endsection
