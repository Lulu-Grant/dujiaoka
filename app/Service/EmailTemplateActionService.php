<?php

namespace App\Service;

use App\Models\Emailtpl;

class EmailTemplateActionService
{
    public function createDefaults(?Emailtpl $template = null): array
    {
        if ($template !== null) {
            return [
                'tpl_name' => $template->tpl_name,
                'tpl_token' => '',
                'tpl_content' => $template->tpl_content,
            ];
        }

        return [
            'tpl_name' => '',
            'tpl_token' => '',
            'tpl_content' => '',
        ];
    }

    public function editDefaults(Emailtpl $template): array
    {
        return [
            'tpl_name' => $template->tpl_name,
            'tpl_token' => $template->tpl_token,
            'tpl_content' => $template->tpl_content,
        ];
    }

    public function previewContext(): array
    {
        return [
            'webname' => '独角数卡西瓜版',
            'weburl' => \site_url(),
            'ord_title' => '【独角数卡西瓜版】订单已处理完成',
            'order_id' => 'XIGUA-20260412-0001',
            'created_at' => '2026-04-12 12:34:56',
            'product_name' => '西瓜会员',
            'buy_amount' => '1',
            'ord_info' => '<p>账号：demo</p><p>密码：123456</p>',
            'ord_price' => '19.90',
        ];
    }

    public function previewTokens(): array
    {
        return [
            ['token' => '{webname}', 'label' => '站点名称', 'sample' => '独角数卡西瓜版'],
            ['token' => '{weburl}', 'label' => '站点地址', 'sample' => \site_url()],
            ['token' => '{ord_title}', 'label' => '订单标题', 'sample' => '【独角数卡西瓜版】订单已处理完成'],
            ['token' => '{order_id}', 'label' => '订单号', 'sample' => 'XIGUA-20260412-0001'],
            ['token' => '{created_at}', 'label' => '时间', 'sample' => '2026-04-12 12:34:56'],
            ['token' => '{product_name}', 'label' => '商品名称', 'sample' => '西瓜会员'],
            ['token' => '{buy_amount}', 'label' => '购买数量', 'sample' => '1'],
            ['token' => '{ord_info}', 'label' => '订单卡密 / 附加信息', 'sample' => '账号：demo<br>密码：123456'],
            ['token' => '{ord_price}', 'label' => '订单金额', 'sample' => '19.90'],
        ];
    }

    public function usageGuide(): array
    {
        return [
            '模板内容支持原生 HTML，也支持 {webname}、{order_id}、{ord_info} 这类占位符。',
            '编辑页右侧会用示例数据实时预览替换后的结果，保存前先确认排版是否正常。',
            '模板标识 tpl_token 创建后建议保持稳定，避免影响已经引用它的通知逻辑。',
        ];
    }

    public function buildPreviewPageData(?Emailtpl $template = null, bool $createMode = false): array
    {
        $isCreatePreview = $createMode || $template === null;
        if ($template === null) {
            $defaults = $this->createDefaults();
        } elseif ($createMode) {
            $defaults = $this->createDefaults($template);
        } else {
            $defaults = $this->editDefaults($template);
        }
        $previewContext = $this->previewContext();

        return [
            'title' => $isCreatePreview ? '新建邮件模板预览 - 后台壳样板' : '邮件模板预览 - 后台壳样板',
            'header' => [
                'kicker' => 'Admin Shell Action',
                'title' => $isCreatePreview ? '新建邮件模板预览' : '邮件模板预览',
                'description' => '这是后台壳中的邮件模板预览页样板。这里会展示模板标题、模板内容、变量提示和渲染后的 HTML 结果，方便在保存前先确认视觉和文案是否正确。',
                'meta' => $isCreatePreview
                    ? '预览页会使用一组示例上下文渲染模板内容；可以先看空白模板的占位提示，再返回创建页继续编辑。'
                    : '预览页会使用一组示例上下文渲染当前模板内容；建议先确认模板标题和正文排版，再返回编辑页保存。',
                'actions' => $isCreatePreview ? [
                    ['label' => '返回创建页', 'href' => admin_url('v2/emailtpl/create'), 'variant' => 'secondary'],
                    ['label' => '返回概览', 'href' => admin_url('v2/emailtpl'), 'variant' => 'secondary'],
                ] : [
                    ['label' => '返回编辑页', 'href' => admin_url('v2/emailtpl/'.$template->id.'/edit'), 'variant' => 'secondary'],
                    ['label' => '查看详情', 'href' => admin_url('v2/emailtpl/'.$template->id), 'variant' => 'secondary'],
                ],
            ],
            'defaults' => $defaults,
            'template' => $template,
            'previewHtml' => $this->renderPreview($defaults['tpl_content'], $previewContext),
            'previewContext' => $previewContext,
            'previewTokens' => $this->previewTokens(),
            'usageGuide' => $this->usageGuide(),
            'summary' => [
                ['label' => '模板标题', 'value' => $defaults['tpl_name'] !== '' ? $defaults['tpl_name'] : '未填写'],
                ['label' => '模板标识', 'value' => $defaults['tpl_token'] !== '' ? $defaults['tpl_token'] : '未填写'],
                ['label' => '内容长度', 'value' => strlen($defaults['tpl_content'])],
            ],
            'rawContent' => $defaults['tpl_content'] === '' ? '当前模板内容为空。可以先返回创建/编辑页输入 HTML，再回来看渲染效果。' : $defaults['tpl_content'],
        ];
    }

    public function renderPreview(string $content, array $context = []): string
    {
        $content = trim($content);

        if ($content === '') {
            return $this->emptyPreviewHtml();
        }

        $rendered = \replace_mail_tpl([
            'tpl_name' => '',
            'tpl_content' => $content,
        ], $context);

        return $rendered['tpl_content'] ?? $content;
    }

    public function emptyPreviewHtml(): string
    {
        return <<<'HTML'
<div style="padding:24px;border:1px dashed #d7e0c9;border-radius:16px;background:#fbfdf7;color:#5a6b50;line-height:1.8;">
    <strong style="display:block;margin-bottom:8px;font-size:15px;color:#24311f;">邮件模板预览</strong>
    <p style="margin:0;">在左侧输入模板内容后，这里会实时显示替换后的预览结果。</p>
    <p style="margin:10px 0 0;">可以直接使用 HTML 和 {webname}、{order_id}、{ord_info} 等占位符。</p>
</div>
HTML;
    }

    public function create(array $payload): Emailtpl
    {
        $template = new Emailtpl();
        $template->tpl_name = $payload['tpl_name'];
        $template->tpl_token = $payload['tpl_token'];
        $template->tpl_content = $payload['tpl_content'];
        $template->save();

        return $template;
    }

    public function update(Emailtpl $template, array $payload): Emailtpl
    {
        $template->tpl_name = $payload['tpl_name'];
        $template->tpl_content = $payload['tpl_content'];
        $template->save();

        return $template->fresh();
    }
}
