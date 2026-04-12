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
            <div class="notice success">
                <strong>维护提示：</strong>
                这里适合补录单条卡密、修复异常库存或调整循环使用标记；如果需要批量新增，请直接去导入卡密页。
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="filters">
                    <label>
                        <span>关联商品</span>
                        <select name="goods_id" required>
                            <option value="">请选择自动发货商品</option>
                            @foreach($goodsOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('goods_id', $defaults['goods_id']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>状态</span>
                        <select name="status" required>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('status', $defaults['status']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_loop" value="1" @if(old('is_loop', $defaults['is_loop'])) checked @endif> 循环使用</span>
                    </label>
                </div>

                <label>
                    <span>卡密内容</span>
                    <textarea name="carmi" rows="10" required>{{ old('carmi', $defaults['carmi']) }}</textarea>
                </label>

                <div class="meta" style="margin-top: 10px;">
                    建议每次只维护一条卡密内容，便于后续核对与回滚；批量卡密请优先使用导入页处理。
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/carmis') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
