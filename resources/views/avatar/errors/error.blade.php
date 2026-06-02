@extends('avatar.layouts.default')

@section('content')
<section class="avatar-error-shell">
    <div class="avatar-empty-state avatar-panel">
        <div class="avatar-empty-code">error</div>
        <h1>{{ __('hyper.error_error') }}</h1>
        <p>{{ $content }}</p>
        @if(!$url)
            <a class="avatar-button avatar-button--secondary" href="javascript:history.back(-1);"><span class="avatar-inline-icon" aria-hidden="true">&lt;</span> {{ __('hyper.error_back_btn') }}</a>
        @else
            <a class="avatar-button avatar-button--secondary" href="{{ $url }}"><span class="avatar-inline-icon" aria-hidden="true">&lt;</span> {{ __('hyper.error_back_btn') }}</a>
        @endif
    </div>
</section>
@stop
