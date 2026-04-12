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
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <label>
                    <span>分类名称</span>
                    <input type="text" name="gp_name" value="{{ old('gp_name', $defaults['gp_name']) }}" required>
                </label>

                <div class="filters">
                    <label>
                        <span>排序</span>
                        <input type="number" min="0" max="999999" name="ord" value="{{ old('ord', $defaults['ord']) }}" required>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open" value="1" @if(old('is_open', $defaults['is_open'])) checked @endif> 启用该分类</span>
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
