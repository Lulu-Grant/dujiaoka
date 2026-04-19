@extends('admin-shell.layout')

@section('title', $title)

@section('content')
    @include('admin-shell.partials.page-header', ['header' => $header])

    <div class="admin-shell-card">
        <div class="admin-shell-card__header">
            <div>
                <h2 class="admin-shell-card__title">批量重生成优惠码内容</h2>
                <p class="admin-shell-card__description">输入优惠码 ID 后，可以先预览命中的优惠码，再统一重生成新的优惠码内容。</p>
            </div>
            <span class="admin-shell-card__badge">低风险维护</span>
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
                    <span class="admin-shell-form__label">优惠码 ID 列表</span>
                    <textarea name="ids_text" rows="6" class="admin-shell-form__textarea" placeholder="一行一个 ID，或使用逗号分隔">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                    <span class="admin-shell-form__hint">支持换行、英文逗号、中文逗号或空格分隔。系统会自动去重并忽略无效 ID。</span>
                    @error('ids_text')
                    <span class="admin-shell-form__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="admin-shell-form__field">
                    <span class="admin-shell-form__label">目标前缀</span>
                    <input type="text" name="prefix" value="{{ old('prefix', $defaults['prefix']) }}" class="admin-shell-form__input" placeholder="例如：XIGUA-SPRING-">
                    <span class="admin-shell-form__hint">留空会回退到默认前缀。</span>
                    @error('prefix')
                    <span class="admin-shell-form__error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="admin-shell-form__field">
                    <span class="admin-shell-form__label">随机段长度</span>
                    <input type="number" name="length" min="4" max="32" value="{{ old('length', $defaults['length']) }}" class="admin-shell-form__input">
                    <span class="admin-shell-form__hint">系统会根据前缀和长度逐个生成新的唯一优惠码内容。</span>
                    @error('length')
                    <span class="admin-shell-form__error">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <div class="admin-shell-form__actions">
                <button type="submit" class="admin-shell-button admin-shell-button--primary">{{ $submitLabel }}</button>
                <a href="{{ admin_url('v2/coupon') }}" class="admin-shell-button admin-shell-button--secondary">返回优惠码概览</a>
            </div>
        </form>
    </div>

    <div class="admin-shell-card">
        <div class="admin-shell-card__header">
            <div>
                <h2 class="admin-shell-card__title">匹配预览</h2>
                <p class="admin-shell-card__description">预览当前输入 ID 命中的优惠码，确认后再统一换码。</p>
            </div>
        </div>

        <div class="admin-shell-detail-grid">
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">请求 ID 数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['requestedCount'] }}</span>
            </div>
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">匹配优惠码数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['matchedCount'] }}</span>
            </div>
            <div class="admin-shell-detail-grid__item">
                <span class="admin-shell-detail-grid__label">未匹配 ID 数</span>
                <span class="admin-shell-detail-grid__value">{{ $context['missingCount'] }}</span>
            </div>
        </div>

        <div class="admin-shell-table-wrap">
            <table class="admin-shell-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>优惠码</th>
                    <th>当前折扣</th>
                    <th>当前可用次数</th>
                    <th>当前使用状态</th>
                    <th>当前启用状态</th>
                </tr>
                </thead>
                <tbody>
                @forelse($context['items'] as $item)
                    <tr>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['code'] }}</td>
                        <td>{{ $item['discount'] }}</td>
                        <td>{{ $item['ret'] }}</td>
                        <td>{{ $item['usage'] }}</td>
                        <td>{{ $item['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="admin-shell-table__empty">当前没有匹配到任何优惠码，先输入有效 ID 再继续。</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
