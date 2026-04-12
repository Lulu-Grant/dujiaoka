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

    @if(!empty($couponModel))
        <section class="panel">
            <div class="panel-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">当前优惠码</div>
                        <div class="detail-value">{!! e($couponModel->coupon) !!}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">使用状态</div>
                        <div class="detail-value">{{ (int) $couponModel->is_use === \App\Models\Coupon::STATUS_USE ? '已使用' : '未使用' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">启用状态</div>
                        <div class="detail-value">{{ (int) $couponModel->is_open === \App\Models\Coupon::STATUS_OPEN ? '已启用' : '已停用' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">关联商品</div>
                        <div class="detail-value">{{ $couponModel->goods->count() }} 个</div>
                    </div>
                </div>
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

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                <div class="notice success" style="margin-bottom: 18px;">
                    建议优惠码前缀：<strong>{{ $couponCodePrefix }}</strong>。优先生成易识别的优惠码，后面查单、测试和复制都更方便。
                </div>

                <label>
                    <span>关联商品</span>
                    <small style="display:block; margin-top:6px; color:#66756b;">按住 Ctrl / Command 可以多选。优惠码通常会绑定一个或多个商品，便于在前台下单时识别适用范围。</small>
                    <select name="goods_ids[]" multiple size="6">
                        @foreach($goodsOptions as $value => $label)
                            <option value="{{ $value }}" @if(in_array((int) $value, collect(old('goods_ids', $defaults['goods_ids']))->map(function ($item) { return (int) $item; })->all(), true)) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="filters">
                    <label>
                        <span>折扣金额</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">这里写绝对减免金额，创建后会直接体现在订单价格里。</small>
                        <input type="number" min="0" step="0.01" name="discount" value="{{ old('discount', $defaults['discount']) }}" required>
                    </label>
                    <label>
                        <span>优惠码</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">建议使用 {{ $couponCodePrefix }}XXXXXX 这种格式，便于人工识别和复制使用。</small>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                            <input
                                id="coupon-code-input"
                                type="text"
                                name="coupon"
                                value="{{ old('coupon', $defaults['coupon']) }}"
                                required
                                style="flex: 1; min-width: 240px;"
                            >
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                @if($isCreate)
                                    <button class="button secondary" type="button" data-coupon-generate data-coupon-suggested="{{ $suggestedCouponCode }}">生成建议码</button>
                                @endif
                                <button class="button secondary" type="button" data-coupon-copy>复制优惠码</button>
                            </div>
                        </div>
                    </label>
                    <label>
                        <span>可用次数</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">设置为 1 表示一次性可用，设置更大的数值则允许重复使用。</small>
                        <input type="number" min="0" name="ret" value="{{ old('ret', $defaults['ret']) }}" required>
                    </label>
                    <label>
                        <span>使用状态</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">一般新建时选择“未使用”，如果你在做数据修正，也可以直接标成已使用。</small>
                        <select name="is_use" required>
                            @foreach($usageOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('is_use', $defaults['is_use']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open" value="1" @if(old('is_open', $defaults['is_open'])) checked @endif> 启用该优惠码</span>
                        <small style="display:block; margin-top:6px; color:#66756b;">停用后不会参与前台选择，但历史记录仍会保留。</small>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/coupon') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var couponInput = document.getElementById('coupon-code-input');
            var generateButton = document.querySelector('[data-coupon-generate]');
            var copyButton = document.querySelector('[data-coupon-copy]');

            if (generateButton && couponInput) {
                generateButton.addEventListener('click', function () {
                    var suggested = generateButton.getAttribute('data-coupon-suggested') || '';
                    if (!suggested) {
                        return;
                    }

                    couponInput.value = suggested;
                    couponInput.focus();
                    couponInput.select();
                });
            }

            if (copyButton && couponInput) {
                copyButton.addEventListener('click', function () {
                    var code = couponInput.value || '';
                    if (!code) {
                        return;
                    }

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code);
                    } else {
                        var textarea = document.createElement('textarea');
                        textarea.value = code;
                        textarea.style.position = 'fixed';
                        textarea.style.left = '-9999px';
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                    }

                    copyButton.textContent = '已复制';
                    setTimeout(function () {
                        copyButton.textContent = '复制优惠码';
                    }, 1600);
                });
            }
        })();
    </script>
@endsection
