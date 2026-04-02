<?php

namespace Tests\Unit;

use App\Models\Emailtpl;
use App\Models\Pay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

require_once __DIR__ . '/../../database/seeds/BootstrapSeeder.php';

class BootstrapSeederBoundaryTest extends TestCase
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

    public function test_bootstrap_seeder_only_restores_bootstrap_data(): void
    {
        Emailtpl::withTrashed()->forceDelete();
        Pay::withTrashed()->forceDelete();

        $seeder = new \BootstrapSeeder();
        $seeder->run();

        $this->assertSame(5, Emailtpl::query()->count());
        $this->assertSame(0, Pay::query()->count());
    }
}
