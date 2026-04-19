@extends('admin-shell.layout')

@section('title', $title)

@section('content')
    @include('admin-shell.partials.page-header', ['header' => $header])

    <div class="admin-shell-card">
        <div class="admin-shell-card__header">
            <div>
                <h2 class="admin-shell-card__title">批量设置订单标题</h2>
                <p class="admin-shell-card__description">输入订单 ID 后，可以先预览命中的订单，再统一更新订单标题。</p>
            </div>
            <span class="admin-shell-card__badge">低风险动作</span>
        </div>

        @if ($errors->any())
            <div class="admin-shell-alert admin-shell-alert--error">
                <strong>提交失败：</strong> 请检查输入内容后重试。
            </div>
        @endif

        @if (session('status'))
            <div class="admin-shell-alert admin-shell-alert--success">
                {{ session('status') }}
            </div>
        @endif

        <form method="post" action="{{ $formAction }}" class="admin-shell-form">
            @csrf

            <div class="admin-shell-form__grid">
                <label class="admin-shell-form__field admin-shell-form__field--full">
                    <span class="admin-shell-form__label">订单 ID</span>
                    <textarea name="ids_text" rows="6" class="admin-shell-form__textarea" placeholder="一行一个 ID，或使用逗号分隔">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    <span class="admin-shell-form__hint">支持换行、英文逗号或中文逗号分隔。系统会自动去重并忽略无效 ID。</span>
                    @error('ids_text')
                    <span class="admin-shell-form__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="admin-shell-form__field admin-shell-form__field--full">
                    <span class="admin-shell-form__label">目标标题</span>
                    <input type="text" name="title" value="{{ old('title', $defaults['title']) }}" class="admin-shell-form__input" placeholder="例如：2026 春季活动人工复核单">
                    <span class="admin-shell-form__hint">提交后会统一覆盖命中订单的标题，适合人工复核标记、活动标签或整理历史订单命名。</span>
                    @error('title')
                    <span class="admin-shell-form__error">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <div class="admin-shell-form__actions">
                <button type="submit" class="admin-shell-button admin-shell-button--primary">{{ $submitLabel }}</button>
                <a href="{{ admin_url('v2/order') }}" class="admin-shell-button admin-shell-button--secondary">返回订单概览</a>
            </div>
        </form>
    </div>

    <div class="admin-shell-card">
        <div class="admin-shell-card__header">
            <div>
                <h2 class="admin-shell-card__title">命中预览</h2>
                <p class="admin-shell-card__description">预览当前输入 ID 命中的订单，确认后再统一更新标题。</p>
            </div>
        </div>

        <div class="admin-shell-detail-grid">
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">请求 ID 数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['requestedCount'] }}</span>
            </div>
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">待处理订单数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['matchedCount'] }}</span>
            </div>
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">未匹配 ID 数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['missingCount'] }}</span>
            </div>
        </div>

        @if (($context['missingCount'] ?? 0) > 0)
            <div class="admin-shell-alert admin-shell-alert--warning">
                以下 ID 没有找到对应订单：{{ implode('、', $context['missingIds']) }}
            </div>
        @endif

        <div class="admin-shell-table-wrap">
            <table class="admin-shell-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>订单号</th>
                    <th>订单标题</th>
                    <th>邮箱</th>
                    <th>订单状态</th>
                    <th>订单类型</th>
                    <th>实付金额</th>
                </tr>
                </thead>
                <tbody>
                @forelse($context['items'] as $item)
                    <tr>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['order_sn'] }}</td>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['email'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['actual_price'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="admin-shell-table__empty">当前没有匹配到任何订单，先输入有效 ID 再继续。</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
