@extends('admin-shell.layout', ['title' => '邮件模板详情 - 后台壳样板'])

@section('content')
    <header class="page-header">
        <div>
            <div class="page-kicker">Admin Shell Sample</div>
            <h1 class="page-title">邮件模板详情</h1>
            <p class="page-description">这张详情页用于固定邮件模板的字段合同，后续新后台壳可以直接复用。</p>
        </div>
        <div class="button-row">
            <a class="button secondary" href="{{ admin_url('v2/emailtpl') }}">返回列表</a>
        </div>
    </header>

    <section class="panel">
        <div class="panel-body detail-grid">
            <div class="detail-item">
                <div class="detail-label">ID</div>
                <div class="detail-value">{{ $template->id }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">邮件标题</div>
                <div class="detail-value">{{ $template->tpl_name }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">邮件标识</div>
                <div class="detail-value">{{ $template->tpl_token }}</div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">邮件内容</div>
                <div class="detail-value" style="white-space: pre-wrap; font-size: 14px; font-weight: 500;">{{ $template->tpl_content }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">创建时间</div>
                <div class="detail-value">{{ $template->created_at }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">更新时间</div>
                <div class="detail-value">{{ $template->updated_at }}</div>
            </div>
        </div>
    </section>
@endsection
