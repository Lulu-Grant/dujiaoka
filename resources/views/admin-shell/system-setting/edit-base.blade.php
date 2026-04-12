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
                        <span>站点标题</span>
                        <input type="text" name="title" value="{{ old('title', $defaults['title']) }}">
                    </label>
                    <label>
                        <span>文字 Logo</span>
                        <input type="text" name="text_logo" value="{{ old('text_logo', $defaults['text_logo']) }}">
                    </label>
                    <label>
                        <span>主题模板</span>
                        <select name="template">
                            @foreach($templateOptions as $value => $label)
                                <option value="{{ $value }}" @if(old('template', $defaults['template']) === $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>默认语言</span>
                        <select name="language">
                            @foreach($languageOptions as $value => $label)
                                <option value="{{ $value }}" @if(old('language', $defaults['language']) === $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>管理邮箱</span>
                        <input type="email" name="manage_email" value="{{ old('manage_email', $defaults['manage_email']) }}">
                    </label>
                    <label>
                        <span>订单过期时间（分钟）</span>
                        <input type="number" min="1" max="1440" name="order_expire_time" value="{{ old('order_expire_time', $defaults['order_expire_time']) }}">
                    </label>
                </div>

                <label style="margin-top: 14px;">
                    <span>站点关键字</span>
                    <input type="text" name="keywords" value="{{ old('keywords', $defaults['keywords']) }}">
                </label>

                <label>
                    <span>站点描述</span>
                    <textarea name="description" rows="6">{{ old('description', $defaults['description']) }}</textarea>
                </label>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">保存基础站点配置</button>
                    <a class="button secondary" href="{{ admin_url('v2/system-setting') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
