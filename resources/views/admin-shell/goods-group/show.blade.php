@extends('admin-shell.layout', ['title' => '商品分类详情 - 后台壳样板'])

@section('content')
    <header class="page-header">
        <div>
            <div class="page-kicker">Admin Shell Sample</div>
            <h1 class="page-title">商品分类详情</h1>
            <p class="page-description">这是商品分类页的详情样板。后续真正替换后台壳时，可以直接照着这组字段合同迁移。</p>
        </div>
        <div class="button-row">
            <a class="button secondary" href="{{ admin_url('v2/goods-group'.($scope ? '?scope='.$scope : '')) }}">返回列表</a>
        </div>
    </header>

    <section class="panel">
        <div class="panel-body detail-grid">
            <div class="detail-item">
                <div class="detail-label">ID</div>
                <div class="detail-value">{{ $group->id }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">分类名称</div>
                <div class="detail-value">{{ $group->gp_name }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">状态</div>
                <div class="detail-value">{{ strip_tags($statusPresenter->openStatusLabel($group->is_open)) }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">排序</div>
                <div class="detail-value">{{ $group->ord }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">商品数</div>
                <div class="detail-value">{{ $group->goods_count }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">创建时间</div>
                <div class="detail-value">{{ $group->created_at }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">更新时间</div>
                <div class="detail-value">{{ $group->updated_at }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">删除状态</div>
                <div class="detail-value">{{ $group->deleted_at ? '已删除' : '正常' }}</div>
            </div>
        </div>
    </section>
@endsection
