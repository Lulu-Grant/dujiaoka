<?php

namespace Tests\Unit;

use App\Service\AdminPageCardService;
use Tests\TestCase;

class AdminPageCardServiceTest extends TestCase
{
    public function test_attach_sets_title_and_card_body(): void
    {
        $content = new class {
            public $title;
            public $body;

            public function title($title)
            {
                $this->title = $title;

                return $this;
            }

            public function body($body)
            {
                $this->body = $body;

                return $this;
            }
        };

        $widget = new \stdClass();
        $result = app(AdminPageCardService::class)->attach($content, 'Test Title', $widget);

        $this->assertSame($content, $result);
        $this->assertSame('Test Title', $content->title);
        $this->assertInstanceOf(\Dcat\Admin\Widgets\Card::class, $content->body);
    }
}
