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
                        <span><input type="checkbox" name="is_open_anti_red" value="1" @if(old('is_open_anti_red', $defaults['is_open_anti_red'])) checked @endif> 开启微信 / QQ 防红</span>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open_img_code" value="1" @if(old('is_open_img_code', $defaults['is_open_img_code'])) checked @endif> 开启图形验证码</span>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open_search_pwd" value="1" @if(old('is_open_search_pwd', $defaults['is_open_search_pwd'])) checked @endif> 开启订单查询密码</span>
                    </label>
                    <label>
                        <span><input type="checkbox" name="is_open_google_translate" value="1" @if(old('is_open_google_translate', $defaults['is_open_google_translate'])) checked @endif> 开启 Google 翻译</span>
                    </label>
                </div>

                <label>
                    <span>站点公告</span>
                    <textarea name="notice" rows="8">{{ old('notice', $defaults['notice']) }}</textarea>
                </label>

                <label>
                    <span>页脚自定义代码</span>
                    <textarea name="footer" rows="8">{{ old('footer', $defaults['footer']) }}</textarea>
                </label>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">保存站点体验配置</button>
                    <a class="button secondary" href="{{ admin_url('v2/system-setting') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
