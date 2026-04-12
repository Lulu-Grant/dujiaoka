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
                        <input type="text" name="title" value="{{ old('title', $defaults['title']) }}" required>
                    </label>
                    <label>
                        <span>文字 Logo</span>
                        <input type="text" name="text_logo" value="{{ old('text_logo', $defaults['text_logo']) }}">
                    </label>
                    <label>
                        <span>图片 Logo 路径</span>
                        <input type="text" name="img_logo" value="{{ old('img_logo', $defaults['img_logo']) }}">
                    </label>
                    <label>
                        <span>默认主题</span>
                        <select name="template" required>
                            @foreach($templateOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('template', $defaults['template']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>默认语言</span>
                        <select name="language" required>
                            @foreach($languageOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('language', $defaults['language']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">保存品牌配置</button>
                    <a class="button secondary" href="{{ admin_url('v2/system-setting') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
