@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @if(!empty($formGuide))
        <section class="panel">
            <div class="panel-body">
                <div class="notice success" style="margin-bottom: 0;">{{ $formGuide }}</div>
            </div>
        </section>
    @endif

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

    <section class="panel">
        <div class="panel-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">默认前缀</div>
                    <div class="detail-value">{{ $couponCodePrefix }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">示例优惠码</div>
                    <div class="detail-value">{!! e($suggestedCouponCode) !!}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">生成方式</div>
                    <div class="detail-value">前缀 + 随机后缀</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">适用场景</div>
                    <div class="detail-value">活动发放、批量测试、分组优惠码</div>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf
                <input type="hidden" name="mode" value="batch">

                <div class="notice success" style="margin-bottom: 18px;">
                    这里会一次生成多条优惠码，建议在生成前先确认商品范围和折扣策略，方便后续直接投放或测试。
                </div>

                <label>
                    <span>关联商品</span>
                    <small style="display:block; margin-top:6px; color:#66756b;">按住 Ctrl / Command 可以多选。批量生成时通常会绑定一组统一适用的商品。</small>
                    <select name="goods_ids[]" multiple size="6">
                        @foreach($goodsOptions as $value => $label)
                            <option value="{{ $value }}" @if(in_array((int) $value, collect(old('goods_ids', $defaults['goods_ids']))->map(function ($item) { return (int) $item; })->all(), true)) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="filters">
                    <label>
                        <span>批量数量</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">一次生成多少条优惠码，建议先从 10 条以内开始。</small>
                        <input type="number" min="1" max="200" name="quantity" value="{{ old('quantity', $defaults['quantity']) }}" required>
                    </label>
                    <label>
                        <span>优惠码前缀</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">留空则使用默认前缀 {{ $couponCodePrefix }}。前缀会直接拼到随机后缀前面。</small>
                        <input type="text" name="prefix" value="{{ old('prefix', $defaults['prefix']) }}" maxlength="64" placeholder="{{ $couponCodePrefix }}">
                    </label>
                    <label>
                        <span>随机后缀长度</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">建议保持 6 到 8 位，既易读又不容易撞码。</small>
                        <input type="number" min="4" max="32" name="length" value="{{ old('length', $defaults['length']) }}" required>
                    </label>
                    <label>
                        <span>折扣金额</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">这里写绝对减免金额，批量创建后会统一写入每条优惠码。</small>
                        <input type="number" min="0" step="0.01" name="discount" value="{{ old('discount', $defaults['discount']) }}" required>
                    </label>
                    <label>
                        <span>可用次数</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">设置为 1 表示一次性可用，设置更大的数值则允许重复使用。</small>
                        <input type="number" min="0" name="ret" value="{{ old('ret', $defaults['ret']) }}" required>
                    </label>
                    <label>
                        <span>使用状态</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">一般批量生成时选择“未使用”，也可以直接做状态修正。</small>
                        <select name="is_use" required>
                            @foreach($usageOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('is_use', $defaults['is_use']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open" value="1" @if(old('is_open', $defaults['is_open'])) checked @endif> 启用这些优惠码</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">停用后不会参与前台选择，但历史记录仍会保留。</small>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/coupon') }}">返回概览</a>
                    <a class="button secondary" href="{{ admin_url('v2/coupon/create') }}">切回单个创建</a>
                </div>
            </form>
        </div>
    </div>
@endsection
