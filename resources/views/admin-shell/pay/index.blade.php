@extends('admin-shell.layout', ['title' => '支付通道管理 - 后台壳样板'])

@section('content')
    <header class="page-header">
        <div>
            <div class="page-kicker">Admin Shell Sample</div>
            <h1 class="page-title">支付通道管理</h1>
            <p class="page-description">这是第一批后台迁移的第三张样板页。支付通道的生命周期、支付方式、支付场景都直接复用现有 presenter 与模型映射。</p>
        </div>
        <div class="meta">共 {{ $pays->total() }} 条通道</div>
    </header>

    <section class="panel">
        <div class="panel-body">
            <form method="get" class="filters">
                <label>
                    ID
                    <input type="number" name="id" value="{{ $filters['id'] }}">
                </label>
                <label>
                    支付标识
                    <input type="text" name="pay_check" value="{{ $filters['pay_check'] }}">
                </label>
                <label>
                    支付名称
                    <input type="text" name="pay_name" value="{{ $filters['pay_name'] }}">
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
                    <a class="button secondary" href="{{ admin_url('v2/pay') }}">重置</a>
                </div>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-body table-wrap">
            @if($pays->count())
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>支付名称</th>
                        <th>支付标识</th>
                        <th>生命周期</th>
                        <th>支付方式</th>
                        <th>支付场景</th>
                        <th>启用状态</th>
                        <th>更新时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pays as $pay)
                        <tr>
                            <td>{{ $pay->id }}</td>
                            <td>{{ $pay->pay_name }}</td>
                            <td>{{ $pay->pay_check }}</td>
                            <td>{!! $presenter->lifecycleBadge($pay->lifecycle) !!}</td>
                            <td>{{ $presenter->methodLabel($pay->pay_method) }}</td>
                            <td>{{ $presenter->clientLabel($pay->pay_client) }}</td>
                            <td>
                                <span class="pill {{ $pay->deleted_at ? 'trashed' : ((int)$pay->is_open ? 'open' : 'closed') }}">
                                    @if($pay->deleted_at)
                                        回收站
                                    @else
                                        {{ strip_tags($presenter->openStatusLabel($pay->is_open)) }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $pay->updated_at }}</td>
                            <td>
                                <a href="{{ admin_url('v2/pay/'.$pay->id.($filters['scope'] ? '?scope='.$filters['scope'] : '')) }}">查看详情</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="pagination">
                    {{ $pays->links() }}
                </div>
            @else
                <div class="empty">当前条件下没有支付通道记录。</div>
            @endif
        </div>
    </section>
@endsection
