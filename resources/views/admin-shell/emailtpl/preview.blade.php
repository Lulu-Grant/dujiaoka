@extends('admin-shell.layout', ['title' => $title])

@section('content')
    <style>
        .email-template-preview-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
            gap: 20px;
            align-items: start;
        }

        .email-template-preview-stack {
            display: grid;
            gap: 16px;
        }

        .email-template-preview-card {
            padding: 16px 18px;
            border: 1px solid #dce4d2;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbf5 100%);
            box-shadow: 0 6px 18px rgba(18, 36, 23, 0.04);
        }

        .email-template-preview-meta {
            margin: 0;
            color: #617160;
            line-height: 1.7;
        }

        .email-template-preview-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .email-template-preview-summary-card {
            padding: 12px 14px;
            border: 1px solid #dce4d2;
            border-radius: 12px;
            background: #fff;
        }

        .email-template-preview-summary-card span {
            display: block;
            color: #6b7e60;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .email-template-preview-summary-card strong {
            display: block;
            margin-top: 6px;
            color: #21311f;
            font-size: 16px;
            line-height: 1.4;
        }

        .email-template-preview-frame {
            width: 100%;
            min-height: 560px;
            border: 1px solid #d7dfce;
            border-radius: 16px;
            background: #fff;
        }

        .email-template-preview-content {
            white-space: pre-wrap;
            line-height: 1.8;
            color: #2f3d2a;
            background: #fff;
            border: 1px solid #dce4d2;
            border-radius: 12px;
            padding: 14px 16px;
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
            .email-template-preview-grid,
            .email-template-preview-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @include('admin-shell.partials.page-header', $header)

    <div class="email-template-preview-grid">
        <div class="email-template-preview-stack">
            <div class="panel">
                <div class="panel-body">
                    <div class="email-template-preview-card">
                        <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7e60;">Summary</div>
                        <h3 style="margin:6px 0 8px;font-size:20px;">模板标题与内容预览</h3>
                        <p class="email-template-preview-meta">这里展示模板的标题、标识、内容长度和渲染后的效果，适合在保存前快速确认排版和变量替换。</p>

                        <div class="email-template-preview-summary">
                            @foreach($summary as $item)
                                <div class="email-template-preview-summary-card">
                                    <span>{{ $item['label'] }}</span>
                                    <strong>{{ $item['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7e60;">Content</div>
                    <h3 style="margin:6px 0 12px;font-size:18px;">原始模板内容</h3>
                    <div class="email-template-preview-content">{{ $rawContent }}</div>
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
        </div>

        <div class="email-template-preview-stack">
            <div class="panel">
                <div class="panel-body">
                    <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7e60;">Preview</div>
                    <h3 style="margin:6px 0 12px;font-size:18px;">渲染后的邮件预览</h3>
                    <iframe
                        class="email-template-preview-frame"
                        title="邮件模板预览"
                        srcdoc="{{ e($previewHtml) }}"
                    ></iframe>
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
@endsection
