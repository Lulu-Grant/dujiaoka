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
                    <span>邮件标题</span>
                    <input type="text" name="tpl_name" value="{{ old('tpl_name', $defaults['tpl_name']) }}" required>
                </label>

                <label>
                    <span>模板标识</span>
                    <input type="text" name="tpl_token" value="{{ old('tpl_token', $defaults['tpl_token']) }}" @if(!$isCreate) readonly @endif required>
                </label>

                <label>
                    <span>邮件内容</span>
                    <textarea name="tpl_content" rows="16" required>{{ old('tpl_content', $defaults['tpl_content']) }}</textarea>
                </label>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/emailtpl') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
