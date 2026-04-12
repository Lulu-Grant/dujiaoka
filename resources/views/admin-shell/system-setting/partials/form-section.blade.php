<section class="panel" style="margin-top: 18px;">
    <div class="panel-body">
        <div style="display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin-bottom: 16px;">
            <div>
                <div class="page-kicker" style="margin-bottom: 6px;">配置分组</div>
                <h2 style="margin: 0; font-size: 18px; line-height: 1.4;">{{ $section['title'] }}</h2>
                @if(!empty($section['description']))
                    <p style="margin: 8px 0 0; color: var(--muted); line-height: 1.75;">
                        {{ $section['description'] }}
                    </p>
                @endif
            </div>

            @if(!empty($section['note']))
                <div style="max-width: 320px; padding: 12px 14px; border-radius: 14px; background: rgba(31, 52, 37, 0.06); color: var(--muted); line-height: 1.7;">
                    {{ $section['note'] }}
                </div>
            @endif
        </div>

        <div class="filters">
            @foreach($section['fields'] as $field)
                @php
                    $fieldType = $field['type'] ?? 'text';
                    $currentValue = old($field['name'], $field['value'] ?? '');
                @endphp

                @if($fieldType === 'textarea')
                    <label @if(!empty($field['wide'])) style="grid-column: 1 / -1;" @endif>
                        <span>{{ $field['label'] }}</span>
                        <textarea
                            name="{{ $field['name'] }}"
                            rows="{{ $field['rows'] ?? 6 }}"
                            @if(!empty($field['required'])) required @endif
                            @if(!empty($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                        >{{ $currentValue }}</textarea>
                        @if(!empty($field['hint']))
                            <small style="display: block; margin-top: 6px; color: var(--muted); line-height: 1.6;">{{ $field['hint'] }}</small>
                        @endif
                    </label>
                @elseif($fieldType === 'select')
                    <label>
                        <span>{{ $field['label'] }}</span>
                        <select
                            name="{{ $field['name'] }}"
                            @if(!empty($field['required'])) required @endif
                        >
                            @foreach(($field['options'] ?? []) as $value => $optionLabel)
                                <option value="{{ $value }}" @if((string) $currentValue === (string) $value) selected @endif>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                        @if(!empty($field['hint']))
                            <small style="display: block; margin-top: 6px; color: var(--muted); line-height: 1.6;">{{ $field['hint'] }}</small>
                        @endif
                    </label>
                @elseif($fieldType === 'checkbox')
                    <label>
                        <span style="display: flex; align-items: center; gap: 10px;">
                            <input
                                type="checkbox"
                                name="{{ $field['name'] }}"
                                value="1"
                                @if(!empty($currentValue)) checked @endif
                            >
                            {{ $field['label'] }}
                        </span>
                        @if(!empty($field['hint']))
                            <small style="display: block; margin-top: 6px; color: var(--muted); line-height: 1.6;">{{ $field['hint'] }}</small>
                        @endif
                    </label>
                @else
                    <label @if(!empty($field['wide'])) style="grid-column: 1 / -1;" @endif>
                        <span>{{ $field['label'] }}</span>
                        <input
                            type="{{ $fieldType }}"
                            name="{{ $field['name'] }}"
                            value="{{ $currentValue }}"
                            @if(!empty($field['required'])) required @endif
                            @if(!empty($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                            @if(!empty($field['min'])) min="{{ $field['min'] }}" @endif
                            @if(!empty($field['max'])) max="{{ $field['max'] }}" @endif
                        >
                        @if(!empty($field['hint']))
                            <small style="display: block; margin-top: 6px; color: var(--muted); line-height: 1.6;">{{ $field['hint'] }}</small>
                        @endif
                    </label>
                @endif
            @endforeach
        </div>
    </div>
</section>
