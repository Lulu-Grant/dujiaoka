<section class="panel">
    <div class="panel-body">
        <form method="get" class="filters">
            @foreach($fields as $field)
                <label>
                    {{ $field['label'] }}
                    @if(($field['type'] ?? 'text') === 'select')
                        <select name="{{ $field['name'] }}">
                            @foreach($field['options'] as $value => $optionLabel)
                                <option value="{{ $value }}" @if((string) ($field['value'] ?? '') === (string) $value) selected @endif>
                                    {{ $optionLabel }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input
                            type="{{ $field['type'] ?? 'text' }}"
                            name="{{ $field['name'] }}"
                            value="{{ $field['value'] ?? '' }}"
                        >
                    @endif
                </label>
            @endforeach

            <div class="button-row">
                <button class="button" type="submit">筛选</button>
                <a class="button secondary" href="{{ $resetUrl }}">重置</a>
            </div>
        </form>
    </div>
</section>
