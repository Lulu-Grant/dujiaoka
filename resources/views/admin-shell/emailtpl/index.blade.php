@extends('admin-shell.layout', ['title' => '邮件模板管理 - 后台壳样板'])

@section('content')
    @include('admin-shell.partials.page-header', [
        'title' => '邮件模板管理',
        'description' => '这是第二张后台壳样板页。当前列表、筛选和详情都通过普通 Laravel 控制器与 Blade 组合，不再依赖 Dcat Grid/Show。',
        'meta' => '共 '.$templates->total().' 条模板',
    ])

    @include('admin-shell.partials.filter-panel', [
        'fields' => [
            ['label' => 'ID', 'name' => 'id', 'type' => 'number', 'value' => $filters['id']],
            ['label' => '邮件标题', 'name' => 'tpl_name', 'value' => $filters['tpl_name']],
            ['label' => '邮件标识', 'name' => 'tpl_token', 'value' => $filters['tpl_token']],
        ],
        'resetUrl' => admin_url('v2/emailtpl'),
    ])

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
