<?php

namespace Tests\Unit;

use App\Models\Emailtpl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

require_once __DIR__ . '/../../database/seeds/EmailTemplateSeeder.php';

class EmailTemplateSeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_bootstrap_email_template_seeder_upserts_default_templates(): void
    {
        Emailtpl::withTrashed()->forceDelete();

        $seeder = new \EmailTemplateSeeder();
        $seeder->run();

        $this->assertSame(5, Emailtpl::query()->count());
        $this->assertNotNull(Emailtpl::query()->where('tpl_token', 'card_send_user_email')->first());
        $this->assertNotNull(Emailtpl::query()->where('tpl_token', 'manual_send_manage_mail')->first());
        $this->assertNotNull(Emailtpl::query()->where('tpl_token', 'failed_order')->first());
        $this->assertNotNull(Emailtpl::query()->where('tpl_token', 'completed_order')->first());
        $this->assertNotNull(Emailtpl::query()->where('tpl_token', 'pending_order')->first());
    }
}
