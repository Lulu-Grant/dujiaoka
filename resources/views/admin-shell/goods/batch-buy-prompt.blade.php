@extends('admin-shell.layout')

@section('title', $title)

@section('content')
    @include('admin-shell.partials.page-header', ['header' => $header])

    <div class="admin-shell-card">
        <div class="admin-shell-card__header">
            <div>
                <h2 class="admin-shell-card__title">批量设置购买提示</h2>
                <p class="admin-shell-card__description">输入商品 ID 后，可以先预览命中的商品，再统一更新购买提示。</p>
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
            <input type="hidden" name="mode" value="batch-buy-prompt">

            <div class="admin-shell-form__grid">
                <label class="admin-shell-form__field admin-shell-form__field--full">
                    <span class="admin-shell-form__label">商品 ID</span>
                    <textarea name="ids_text" rows="6" class="admin-shell-form__textarea" placeholder="一行一个 ID，或使用逗号分隔">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    <span class="admin-shell-form__hint">支持换行、英文逗号或中文逗号分隔。系统会自动去重并忽略无效 ID。</span>
                    @error('ids_text')
                    <span class="admin-shell-form__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="admin-shell-form__field admin-shell-form__field--full">
                    <span class="admin-shell-form__label">目标购买提示</span>
                    <textarea name="buy_prompt" rows="5" class="admin-shell-form__textarea" placeholder="例如：购买后请在 10 分钟内完成查单并保存卡密">{{ old('buy_prompt', $defaults['buy_prompt']) }}</textarea>
                    <span class="admin-shell-form__hint">本动作只更新前台购买提示，不影响价格、库存、分类、类型和启用状态。</span>
                    @error('buy_prompt')
                    <span class="admin-shell-form__error">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <div class="admin-shell-form__actions">
                <button type="submit" class="admin-shell-button admin-shell-button--primary">{{ $submitLabel }}</button>
                <a href="{{ admin_url('v2/goods') }}" class="admin-shell-button admin-shell-button--secondary">返回商品概览</a>
            </div>
        </form>
    </div>

    <div class="admin-shell-card">
        <div class="admin-shell-card__header">
            <div>
                <h2 class="admin-shell-card__title">命中预览</h2>
                <p class="admin-shell-card__description">预览当前输入 ID 命中的商品，确认后再统一写入购买提示。</p>
            </div>
        </div>

        <div class="admin-shell-detail-grid">
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">请求 ID 数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['requestedCount'] }}</span>
            </div>
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">待处理商品数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['matchedCount'] }}</span>
            </div>
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">缺失 ID 数</span>
                <span class="admin-shell-detail-grid__value">{{ count($context['missingIds']) }}</span>
            </div>
        </div>

        @if (!empty($context['missingIds']))
            <div class="admin-shell-alert admin-shell-alert--warning">
                以下 ID 没有找到对应商品：{{ implode('、', $context['missingIds']) }}
            </div>
        @endif

        <div class="admin-shell-table-wrap">
            <table class="admin-shell-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>商品名称</th>
                    <th>商品类型</th>
                    <th>启用状态</th>
                    <th>当前购买提示</th>
                </tr>
                </thead>
                <tbody>
                @forelse($context['items'] as $item)
                    <tr>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td>{{ $item['buy_prompt'] === '' ? '未设置' : $item['buy_prompt'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-shell-table__empty">当前没有匹配到任何商品，先输入有效 ID 再继续。</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
