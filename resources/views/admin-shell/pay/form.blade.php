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
            <div class="page-kicker">维护摘要</div>
            <h2 class="page-title" style="font-size: 24px; margin-top: 6px;">{{ $context['summaryTitle'] }}</h2>
            <p class="page-description">{{ $context['summaryDescription'] }}</p>

            <div class="detail-grid" style="margin-top: 20px;">
                @foreach($context['summaryItems'] as $item)
                    <div class="detail-item">
                        <div class="detail-label">{{ $item['label'] }}</div>
                        <div class="detail-value">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="meta" style="margin-top: 18px;">
                可编辑字段：{{ implode('、', $context['editableFields']) }}
            </div>
            <div class="meta" style="margin-top: 8px;">
                {{ $context['notice'] }}
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack">
                @csrf

                @foreach($sections as $section)
                    @include('admin-shell.pay.partials.form-section', ['section' => $section])
                @endforeach

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/pay') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
