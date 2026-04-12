@extends('admin-shell.layout', ['title' => '邮件模板管理 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    <div class="panel">
        <div class="panel-body">
            <strong style="display:block;margin-bottom:6px;">模板使用提示</strong>
            <p style="margin:0;color:#5f6e59;line-height:1.7;">邮件模板支持 HTML 和 {webname}、{order_id}、{ord_info} 等占位符。进入编辑页后，可以在右侧实时查看替换结果。</p>
        </div>
    </div>

    @include('admin-shell.partials.filter-panel', $filterPanel)

    @include('admin-shell.partials.data-table', $table)
@endsection
