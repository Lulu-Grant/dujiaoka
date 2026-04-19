@extends('admin-shell.layout')

@section('content')
    <section class="admin-shell-stack">
        <header class="admin-shell-page-header">
            <div>
                <p class="admin-shell-page-header__kicker">{{ $header['kicker'] }}</p>
                <h1>{{ $header['title'] }}</h1>
                <p class="admin-shell-page-header__description">{{ $header['description'] }}</p>
                <p class="admin-shell-page-header__meta">{{ $header['meta'] }}</p>
            </div>
            <div class="admin-shell-page-header__actions">
                @foreach($header['actions'] as $action)
                    <a href="{{ $action['href'] }}" class="admin-shell-button {{ ($action['variant'] ?? 'primary') === 'secondary' ? 'admin-shell-button--secondary' : '' }}">
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </header>

        <section class="admin-shell-card">
            <div class="admin-shell-card__body">
                @if(session('status'))
                    <div class="notice success">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="notice error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ $formAction }}" class="admin-shell-form admin-shell-stack">
                    @csrf
                    <input type="hidden" name="mode" value="batch-description">

                    <div class="admin-shell-form__field">
                        <label class="admin-shell-form__label" for="ids_text">商品 ID</label>
                        <textarea id="ids_text" name="ids_text" rows="5" class="admin-shell-form__textarea" placeholder="例如：1001,1002 或者每行一个 ID">{{ old('ids_text', $defaults['ids_text']) }}</textarea>
                        <p class="admin-shell-form__hint">支持换行、逗号和空格混输。保存时会自动去重。</p>
                    </div>

                    <div class="admin-shell-form__field">
                        <label class="admin-shell-form__label" for="description">目标商品说明</label>
                        <textarea id="description" name="description" rows="8" class="admin-shell-form__textarea" placeholder="例如：发货后请优先保存卡密，如遇异常请在 24 小时内联系客服">{{ old('description', $defaults['description']) }}</textarea>
                        <p class="admin-shell-form__hint">这里会统一覆盖命中商品的说明内容，适合活动说明、售后须知和交付细节统一更新。</p>
                        @error('description')
                            <p class="admin-shell-form__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="admin-shell-form__actions">
                        <button type="submit" class="admin-shell-button">{{ $submitLabel }}</button>
                    </div>
                </form>
            </div>
        </section>

        @if(!empty($context['missingIds']))
            <section class="admin-shell-card">
                <div class="admin-shell-card__header">
                    <h2>缺失 ID</h2>
                    <p class="admin-shell-card__description">这些 ID 不会被更新，先确认是不是录入时写错了。</p>
                </div>
                <div class="admin-shell-card__body">
                    <div class="admin-shell-badge-list">
                        @foreach($context['missingIds'] as $missingId)
                            <span class="admin-shell-badge">{{ $missingId }}</span>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="admin-shell-card">
            <div class="admin-shell-card__header">
                <h2>命中商品预览</h2>
                <p class="admin-shell-card__description">先看一眼商品名称、类型、当前状态和当前说明，再执行批量更新，避免粘错 ID。</p>
            </div>
            <div class="admin-shell-card__body">
                <div class="admin-shell-stats-grid">
                    <article class="admin-shell-stat">
                        <span class="admin-shell-stat__label">待处理商品数</span>
                        <strong class="admin-shell-stat__value">{{ $context['matchedCount'] }}</strong>
                    </article>
                    <article class="admin-shell-stat">
                        <span class="admin-shell-stat__label">输入 ID 数</span>
                        <strong class="admin-shell-stat__value">{{ $context['requestedCount'] }}</strong>
                    </article>
                    <article class="admin-shell-stat">
                        <span class="admin-shell-stat__label">缺失 ID 数</span>
                        <strong class="admin-shell-stat__value">{{ count($context['missingIds'] ?? []) }}</strong>
                    </article>
                </div>

                <div class="admin-shell-table-wrapper" style="margin-top: 18px;">
                    <table class="admin-shell-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>商品名称</th>
                            <th>类型</th>
                            <th>状态</th>
                            <th>当前商品说明</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($context['items'] as $item)
                            <tr>
                                <td>{{ $item['id'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['type'] }}</td>
                                <td>{{ $item['status'] }}</td>
                                <td>{{ $item['description'] === '' ? '未设置' : $item['description'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-shell-table__empty">当前没有匹配到任何商品，请先输入有效的商品 ID。</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </section>
@endsection
