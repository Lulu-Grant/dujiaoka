@extends('admin-shell.layout', ['title' => $title])

@section('content')
    <style>
        .email-template-shell-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 20px;
            align-items: start;
        }

        .email-template-panel-stack {
            display: grid;
            gap: 16px;
        }

        .email-template-preview-frame {
            width: 100%;
            min-height: 560px;
            border: 1px solid #d7dfce;
            border-radius: 16px;
            background: #fff;
        }

        .email-template-guide-list {
            margin: 0;
            padding-left: 18px;
            color: #42513a;
            line-height: 1.8;
        }

        .email-template-token-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .email-template-token-card {
            padding: 12px 14px;
            border: 1px solid #dce4d2;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbf5 100%);
            box-shadow: 0 6px 18px rgba(18, 36, 23, 0.04);
        }

        .email-template-token-card code {
            display: inline-block;
            margin-bottom: 6px;
            font-weight: 700;
            color: #1d6b3d;
            background: #eef8f0;
            padding: 3px 8px;
            border-radius: 999px;
        }

        .email-template-token-card span {
            display: block;
            font-weight: 700;
            color: #21311f;
        }

        .email-template-token-card small {
            display: block;
            margin-top: 4px;
            color: #617160;
            line-height: 1.6;
        }

        @media (max-width: 1180px) {
            .email-template-shell-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

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

    <div class="email-template-shell-grid">
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
                        <small style="display:block;margin-top:8px;color:#647067;line-height:1.6;">模板标识创建后建议保持稳定，邮件内容支持 {webname}、{order_id}、{ord_info} 等占位符。</small>
                    </label>

                    <label>
                        <span>邮件内容</span>
                        <textarea
                            name="tpl_content"
                            rows="20"
                            required
                            data-email-template-editor
                            style="min-height: 560px;"
                        >{{ old('tpl_content', $defaults['tpl_content']) }}</textarea>
                    </label>

                    <div class="button-row" style="margin-top: 16px;">
                        <button class="button" type="submit">{{ $submitLabel }}</button>
                        <a class="button secondary" href="{{ admin_url('v2/emailtpl') }}">返回概览</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="email-template-panel-stack">
            <div class="panel">
                <div class="panel-body">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;">
                        <div>
                            <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7e60;">Preview</div>
                            <h3 style="margin:6px 0 6px;font-size:18px;">邮件模板预览</h3>
                            <p style="margin:0;color:#66745f;line-height:1.7;">这里会实时替换占位符并展示渲染效果，方便你在保存前检查 HTML 排版和文案是否正确。</p>
                        </div>
                    </div>

                    <iframe
                        id="email-template-preview-frame"
                        class="email-template-preview-frame"
                        title="邮件模板预览"
                        srcdoc="{{ e($previewHtml) }}"
                    ></iframe>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7e60;">Guide</div>
                    <h3 style="margin:6px 0 12px;font-size:18px;">使用说明</h3>
                    <ul class="email-template-guide-list">
                        @foreach($usageGuide as $guide)
                            <li>{{ $guide }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7e60;">Tokens</div>
                    <h3 style="margin:6px 0 12px;font-size:18px;">占位符参考</h3>
                    <div class="email-template-token-grid">
                        @foreach($previewTokens as $token)
                            <div class="email-template-token-card">
                                <code>{{ $token['token'] }}</code>
                                <span>{{ $token['label'] }}</span>
                                <small>{{ $token['sample'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var editor = document.querySelector('[data-email-template-editor]');
            var frame = document.getElementById('email-template-preview-frame');
            if (!editor || !frame) {
                return;
            }

            var context = @json($previewContext);
            var emptyPreview = @json($previewHtml);

            function render(source) {
                var output = source || '';

                Object.keys(context).forEach(function (key) {
                    var token = '{' + key + '}';
                    output = output.split(token).join(context[key]);
                });

                return output.trim() === '' ? emptyPreview : output;
            }

            function refresh() {
                frame.srcdoc = render(editor.value);
            }

            editor.addEventListener('input', refresh);
            refresh();
        })();
    </script>
@endsection
