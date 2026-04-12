@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @if(session('status'))
        <div class="panel">
            <div class="panel-body">
                <div class="notice success">{{ session('status') }}</div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="panel">
            <div class="panel-body">
                <div class="notice error">{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <div class="panel">
        <div class="panel-body">
            @if(!empty($group))
                @include('admin-shell.partials.detail-grid', ['items' => [
                    ['label' => '当前状态', 'value' => sprintf('<span class="pill %s">%s</span><span style="margin-left: 10px; color: #66756b;">%s</span>', $group->is_open ? 'open' : 'closed', $group->is_open ? '已启用' : '已停用', $group->is_open ? '前台可见' : '前台隐藏')],
                    ['label' => '排序', 'value' => sprintf('<strong>%d</strong><span style="margin-left: 10px; color: #66756b;">数字越小越靠前</span>', (int) $group->ord)],
                    ['label' => '商品数', 'value' => sprintf('<strong>%d</strong><span style="margin-left: 10px; color: #66756b;">%s</span>', (int) $group->goods_count, (int) $group->goods_count > 0 ? '已有商品挂载' : '当前未关联商品')],
                    ['label' => '更新时间', 'value' => e((string) $group->updated_at)],
                ]])
            @endif

            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <label>
                    <span>分类名称</span>
                    <small style="display:block; margin-top:6px; color:#66756b;">用于前台和后台展示，建议直接使用能识别的分类名。</small>
                    <input type="text" name="gp_name" value="{{ old('gp_name', $defaults['gp_name']) }}" required>
                </label>

                <div class="filters">
                    <label>
                        <span>排序</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">数字越小越靠前，建议从 1 开始，便于后续调整。</small>
                        <input type="number" min="0" max="999999" name="ord" value="{{ old('ord', $defaults['ord']) }}" required>
                    </label>
                    <label>
                        <span>状态</span>
                        <div style="display: flex; gap: 14px; margin-top: 8px; flex-wrap: wrap;">
                            <label style="display: inline-flex; align-items: center; gap: 8px;">
                                <input type="radio" name="is_open" value="1" @if((string) old('is_open', $defaults['is_open']) === '1') checked @endif>
                                <span>启用</span>
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 8px;">
                                <input type="radio" name="is_open" value="0" @if((string) old('is_open', $defaults['is_open']) === '0') checked @endif>
                                <span>停用</span>
                            </label>
                        </div>
                        <small style="display:block; margin-top:6px; color:#66756b;">启用后前台可见，停用后不参与前台选择。</small>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/goods-group') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
