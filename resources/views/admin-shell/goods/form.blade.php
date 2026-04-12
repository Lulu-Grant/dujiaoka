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

                @foreach($sections as $section)
                    <section class="panel" style="box-shadow: none; border-radius: 22px; border-color: rgba(77, 106, 87, 0.12);">
                        <div class="panel-body">
                            <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
                                <div>
                                    <div style="font-size: 18px; font-weight: 800; color: var(--shell-ink);">{{ $section['title'] }}</div>
                                    <p class="meta" style="margin: 6px 0 0;">{{ $section['description'] }}</p>
                                </div>

                                @if(!empty($section['note']))
                                    <div class="page-meta" style="max-width: 360px;">{{ $section['note'] }}</div>
                                @endif
                            </div>

                            <div class="filters" style="align-items: start;">
                                @foreach($section['fields'] as $field)
                                    @php
                                        $fieldValue = old($field['name'], $field['value']);
                                        $isWide = !empty($field['span']) || in_array(($field['type'] ?? 'text'), ['textarea', 'multiselect'], true);
                                    @endphp

                                    @if(($field['type'] ?? 'text') === 'checkbox')
                                        <div @if($isWide) style="grid-column: 1 / -1;" @endif>
                                            <label style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-radius: 16px; border: 1px solid var(--shell-line-strong); background: rgba(255, 255, 255, 0.84);">
                                                <input type="hidden" name="{{ $field['name'] }}" value="0">
                                                <input type="checkbox" name="{{ $field['name'] }}" value="1" @if((int) $fieldValue === 1) checked @endif>
                                                <span style="display: grid; gap: 4px;">
                                                    <strong style="font-size: 14px; color: var(--shell-ink);">{{ $field['label'] }}</strong>
                                                    @if(!empty($field['help']))
                                                        <small style="color: var(--shell-muted); line-height: 1.5;">{{ $field['help'] }}</small>
                                                    @endif
                                                </span>
                                            </label>
                                        </div>
                                        @continue
                                    @endif

                                    <label @if($isWide) style="grid-column: 1 / -1;" @endif>
                                        <span>
                                            {{ $field['label'] }}
                                            @if(!empty($field['required']))
                                                <strong style="color: var(--shell-danger);">*</strong>
                                            @endif
                                        </span>

                                        @if(($field['type'] ?? 'text') === 'select')
                                            <select name="{{ $field['name'] }}" @if(!empty($field['required'])) required @endif>
                                                @foreach($field['options'] as $value => $label)
                                                    <option value="{{ $value }}" @if((string) $fieldValue === (string) $value) selected @endif>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'multiselect')
                                            @php
                                                $selectedValues = collect(is_array($fieldValue) ? $fieldValue : [])->map(function ($item) {
                                                    return (int) $item;
                                                })->all();
                                            @endphp
                                            <select name="{{ $field['name'] }}[]" multiple size="{{ $field['size'] ?? 6 }}">
                                                @foreach($field['options'] as $value => $label)
                                                    <option value="{{ $value }}" @if(in_array((int) $value, $selectedValues, true)) selected @endif>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($field['type'] ?? 'text') === 'textarea')
                                            <textarea name="{{ $field['name'] }}" rows="{{ $field['rows'] ?? 4 }}" @if(!empty($field['required'])) required @endif>{{ $fieldValue }}</textarea>
                                        @else
                                            <input
                                                type="{{ $field['type'] ?? 'text' }}"
                                                name="{{ $field['name'] }}"
                                                value="{{ $fieldValue }}"
                                                @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                                @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                                @if(!empty($field['required'])) required @endif
                                            >
                                        @endif

                                        @if(!empty($field['help']))
                                            <small style="color: var(--shell-muted); line-height: 1.5;">{{ $field['help'] }}</small>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">{{ $submitLabel }}</button>
                    <a class="button secondary" href="{{ admin_url('v2/goods') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
