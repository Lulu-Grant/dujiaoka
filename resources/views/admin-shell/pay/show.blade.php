@extends('admin-shell.layout', ['title' => '支付通道详情 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @include('admin-shell.partials.detail-grid', ['items' => $items])
@endsection
