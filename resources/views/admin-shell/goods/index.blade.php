@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    <section class="panel">
        <div class="panel-body">
            <div class="notice success" style="margin-bottom: 0;">
                {{ $maintenanceNote ?? '商品壳页优先用于查找、核对和进入编辑页。复杂库存或批量动作，建议先确认详情再保存。' }}
            </div>
        </div>
    </section>

    @include('admin-shell.partials.filter-panel', $filterPanel)
    @include('admin-shell.partials.data-table', $table)
@endsection
