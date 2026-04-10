@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)
    @include('admin-shell.partials.filter-panel', $filterPanel)
    @include('admin-shell.partials.data-table', $table)
@endsection
