@extends('admin-shell.layout', ['title' => '邮件模板管理 - 后台壳样板'])

@section('content')
    <header class="page-header">
        <div>
            <div class="page-kicker">Admin Shell Sample</div>
            <h1 class="page-title">邮件模板管理</h1>
            <p class="page-description">这是第二张后台壳样板页。当前列表、筛选和详情都通过普通 Laravel 控制器与 Blade 组合，不再依赖 Dcat Grid/Show。</p>
        </div>
        <div class="meta">共 {{ $templates->total() }} 条模板</div>
    </header>

    <section class="panel">
        <div class="panel-body">
            <form method="get" class="filters">
                <label>
                    ID
                    <input type="number" name="id" value="{{ $filters['id'] }}">
                </label>
                <label>
                    邮件标题
                    <input type="text" name="tpl_name" value="{{ $filters['tpl_name'] }}">
                </label>
                <label>
                    邮件标识
                    <input type="text" name="tpl_token" value="{{ $filters['tpl_token'] }}">
                </label>
                <div class="button-row">
                    <button class="button" type="submit">筛选</button>
                    <a class="button secondary" href="{{ admin_url('v2/emailtpl') }}">重置</a>
                </div>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-body table-wrap">
            @if($templates->count())
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>邮件标题</th>
                        <th>邮件标识</th>
                        <th>创建时间</th>
                        <th>更新时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td>{{ $template->id }}</td>
                            <td>{{ $template->tpl_name }}</td>
                            <td>{{ $template->tpl_token }}</td>
                            <td>{{ $template->created_at }}</td>
                            <td>{{ $template->updated_at }}</td>
                            <td>
                                <a href="{{ admin_url('v2/emailtpl/'.$template->id) }}">查看详情</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="pagination">
                    {{ $templates->links() }}
                </div>
            @else
                <div class="empty">当前条件下没有邮件模板记录。</div>
            @endif
        </div>
    </section>
@endsection
