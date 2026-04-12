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

                <div class="filters">
                    <label>
                        <span>商品名称</span>
                        <input type="text" name="gd_name" value="{{ old('gd_name', $defaults['gd_name']) }}" required>
                    </label>
                    <label>
                        <span>所属分类</span>
                        <select name="group_id" required>
                            <option value="">请选择分类</option>
                            @foreach($groupOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('group_id', $defaults['group_id']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>商品类型</span>
                        <select name="type" required>
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('type', $defaults['type']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>图片路径</span>
                        <input type="text" name="picture" value="{{ old('picture', $defaults['picture']) }}">
                    </label>
                    <label>
                        <span>商品简介</span>
                        <input type="text" name="gd_description" value="{{ old('gd_description', $defaults['gd_description']) }}" required>
                    </label>
                    <label>
                        <span>商品关键字</span>
                        <input type="text" name="gd_keywords" value="{{ old('gd_keywords', $defaults['gd_keywords']) }}" required>
                    </label>
                </div>

                <div class="filters">
                    <label>
                        <span>原价</span>
                        <input type="number" min="0" step="0.01" name="retail_price" value="{{ old('retail_price', $defaults['retail_price']) }}" required>
                    </label>
                    <label>
                        <span>售价</span>
                        <input type="number" min="0" step="0.01" name="actual_price" value="{{ old('actual_price', $defaults['actual_price']) }}" required>
                    </label>
                    <label>
                        <span>库存</span>
                        <input type="number" min="0" name="in_stock" value="{{ old('in_stock', $defaults['in_stock']) }}" required>
                    </label>
                    <label>
                        <span>销量</span>
                        <input type="number" min="0" name="sales_volume" value="{{ old('sales_volume', $defaults['sales_volume']) }}" required>
                    </label>
                    <label>
                        <span>限购数量</span>
                        <input type="number" min="0" name="buy_limit_num" value="{{ old('buy_limit_num', $defaults['buy_limit_num']) }}" required>
                    </label>
                    <label>
                        <span>排序</span>
                        <input type="number" min="0" name="ord" value="{{ old('ord', $defaults['ord']) }}" required>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open" value="1" @if(old('is_open', $defaults['is_open'])) checked @endif> 启用该商品</span>
                    </label>
                </div>

                <label>
                    <span>关联优惠码</span>
                    <select name="coupon_ids[]" multiple size="6">
                        @foreach($couponOptions as $value => $label)
                            <option value="{{ $value }}" @if(in_array((int) $value, collect(old('coupon_ids', $defaults['coupon_ids']))->map(function ($item) { return (int) $item; })->all(), true)) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>购买提示</span>
                    <textarea name="buy_prompt" rows="4">{{ old('buy_prompt', $defaults['buy_prompt']) }}</textarea>
                </label>

                <label>
                    <span>商品说明</span>
                    <textarea name="description" rows="8">{{ old('description', $defaults['description']) }}</textarea>
                </label>

                <label>
                    <span>更多输入配置</span>
                    <textarea name="other_ipu_cnf" rows="6">{{ old('other_ipu_cnf', $defaults['other_ipu_cnf']) }}</textarea>
                </label>

                <label>
                    <span>批发价配置</span>
                    <textarea name="wholesale_price_cnf" rows="6">{{ old('wholesale_price_cnf', $defaults['wholesale_price_cnf']) }}</textarea>
                </label>

                <label>
                    <span>API Hook</span>
                    <textarea name="api_hook" rows="4">{{ old('api_hook', $defaults['api_hook']) }}</textarea>
                </label>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/goods') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
