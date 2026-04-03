<?php

namespace Tests\Unit;

use App\Service\AdminFormBehaviorService;
use Tests\TestCase;

class AdminFormBehaviorServiceTest extends TestCase
{
    public function test_email_template_token_field_mode_changes_between_create_and_edit(): void
    {
        $service = app(AdminFormBehaviorService::class);

        $this->assertSame(
            ['required' => true, 'disabled' => false],
            $service->emailTemplateTokenFieldMode(true)
        );
        $this->assertSame(
            ['required' => false, 'disabled' => true],
            $service->emailTemplateTokenFieldMode(false)
        );
    }

    public function test_disable_view_check_invokes_footer_capability(): void
    {
        $footer = new class {
            public $disabled = false;

            public function disableViewCheck(): void
            {
                $this->disabled = true;
            }
        };

        app(AdminFormBehaviorService::class)->disableViewCheck($footer);

        $this->assertTrue($footer->disabled);
    }
}
