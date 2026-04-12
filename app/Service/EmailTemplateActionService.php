<?php

namespace App\Service;

use App\Models\Emailtpl;

class EmailTemplateActionService
{
    public function createDefaults(): array
    {
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
