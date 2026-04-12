<?php

namespace Tests\Unit;

use App\Models\Emailtpl;
use App\Service\EmailTemplateActionService;
use Tests\TestCase;

class EmailTemplateActionServiceTest extends TestCase
{
    public function test_preview_context_exposes_expected_tokens(): void
    {
        $service = $this->app->make(EmailTemplateActionService::class);
        $context = $service->previewContext();

        $this->assertSame('独角数卡西瓜版', $context['webname']);
        $this->assertArrayHasKey('order_id', $context);
        $this->assertArrayHasKey('ord_info', $context);
    }

    public function test_render_preview_replaces_tokens_and_keeps_html(): void
    {
        $service = $this->app->make(EmailTemplateActionService::class);
        $html = $service->renderPreview('<p>{webname}</p><p>{order_id}</p>', $service->previewContext());

        $this->assertStringContainsString('独角数卡西瓜版', $html);
        $this->assertStringContainsString('XIGUA-20260412-0001', $html);
    }

    public function test_build_preview_page_data_for_create_uses_empty_defaults(): void
    {
        $service = $this->app->make(EmailTemplateActionService::class);
        $page = $service->buildPreviewPageData();

        $this->assertSame('新建邮件模板预览 - 后台壳样板', $page['title']);
        $this->assertSame('新建邮件模板预览', $page['header']['title']);
        $this->assertSame('', $page['defaults']['tpl_name']);
        $this->assertSame('当前模板内容为空。可以先返回创建/编辑页输入 HTML，再回来看渲染效果。', $page['rawContent']);
        $this->assertCount(3, $page['summary']);
    }

    public function test_build_preview_page_data_for_edit_uses_template_values(): void
    {
        $service = $this->app->make(EmailTemplateActionService::class);
        $template = new Emailtpl();
        $template->tpl_name = '测试模板';
        $template->tpl_token = 'test-token';
        $template->tpl_content = '<p>{order_id}</p>';
        $template->id = 321;

        $page = $service->buildPreviewPageData($template);

        $this->assertSame('邮件模板预览 - 后台壳样板', $page['title']);
        $this->assertSame('邮件模板预览', $page['header']['title']);
        $this->assertSame('测试模板', $page['summary'][0]['value']);
        $this->assertSame('test-token', $page['summary'][1]['value']);
        $this->assertStringContainsString('XIGUA-20260412-0001', $page['previewHtml']);
        $this->assertStringContainsString('返回编辑页', $page['header']['actions'][0]['label']);
    }
}
