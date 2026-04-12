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
                    <span>关联商品</span>
                    <select name="goods_ids[]" multiple size="6">
                        @foreach($goodsOptions as $value => $label)
                            <option value="{{ $value }}" @if(in_array((int) $value, collect(old('goods_ids', $defaults['goods_ids']))->map(function ($item) { return (int) $item; })->all(), true)) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="filters">
                    <label>
                        <span>折扣金额</span>
                        <input type="number" min="0" step="0.01" name="discount" value="{{ old('discount', $defaults['discount']) }}" required>
                    </label>
                    <label>
                        <span>优惠码</span>
                        <input type="text" name="coupon" value="{{ old('coupon', $defaults['coupon']) }}" required>
                    </label>
                    <label>
                        <span>可用次数</span>
                        <input type="number" min="0" name="ret" value="{{ old('ret', $defaults['ret']) }}" required>
                    </label>
                    <label>
                        <span>使用状态</span>
                        <select name="is_use" required>
                            @foreach($usageOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('is_use', $defaults['is_use']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open" value="1" @if(old('is_open', $defaults['is_open'])) checked @endif> 启用该优惠码</span>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/coupon') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
