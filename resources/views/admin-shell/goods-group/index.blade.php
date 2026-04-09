@extends('admin-shell.layout', ['title' => '商品分类管理 - 后台壳样板'])

@section('content')
    <header class="page-header">
        <div>
            <div class="page-kicker">Admin Shell Sample</div>
            <h1 class="page-title">商品分类管理</h1>
            <p class="page-description">这是第一批后台迁移样板页。当前使用普通 Laravel 控制器、服务和 Blade 渲染，不再依赖 Dcat Grid。</p>
        </div>
        <div class="meta">共 {{ $groups->total() }} 条记录</div>
    </header>

    <section class="panel">
        <div class="panel-body">
            <form method="get" class="filters">
                <label>
                    ID
                    <input type="number" name="id" value="{{ $filters['id'] }}">
                </label>
                <label>
                    范围
                    <select name="scope">
                        <option value="">全部</option>
                        <option value="trashed" @if(($filters['scope'] ?? null) === 'trashed') selected @endif>回收站</option>
                    </select>
                </label>
                <div class="button-row">
                    <button class="button" type="submit">筛选</button>
                    <a class="button secondary" href="{{ admin_url('v2/goods-group') }}">重置</a>
                </div>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-body table-wrap">
            @if($groups->count())
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>分类名称</th>
                        <th>状态</th>
                        <th>排序</th>
                        <th>商品数</th>
                        <th>创建时间</th>
                        <th>更新时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($groups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td>{{ $group->gp_name }}</td>
                            <td>
                                <span class="pill {{ $group->deleted_at ? 'trashed' : ((int)$group->is_open ? 'open' : 'closed') }}">
                                    @if($group->deleted_at)
                                        回收站
                                    @else
                                        {{ strip_tags($statusPresenter->openStatusLabel($group->is_open)) }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $group->ord }}</td>
                            <td>{{ $group->goods_count }}</td>
                            <td>{{ $group->created_at }}</td>
                            <td>{{ $group->updated_at }}</td>
                            <td>
                                <a href="{{ admin_url('v2/goods-group/'.$group->id.($filters['scope'] ? '?scope='.$filters['scope'] : '')) }}">查看详情</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="pagination">
                    {{ $groups->links() }}
                </div>
            @else
                <div class="empty">当前条件下没有商品分类记录。</div>
            @endif
        </div>
    </section>
@endsection
