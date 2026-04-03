<?php

namespace Tests\Unit;

use App\Service\AdminTextareaPresenterService;
use Tests\TestCase;

class AdminTextareaPresenterServiceTest extends TestCase
{
    public function test_render_escapes_html_content(): void
    {
        $html = app(AdminTextareaPresenterService::class)->render('<script>alert(1)</script>');

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('<textarea', $html);
    }
}
