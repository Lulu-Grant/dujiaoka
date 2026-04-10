@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)
    @include('admin-shell.partials.detail-grid', ['items' => $items])
@endsection
