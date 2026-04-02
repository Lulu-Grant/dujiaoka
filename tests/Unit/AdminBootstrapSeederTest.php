<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

require_once __DIR__ . '/../../database/seeds/AdminBootstrapSeeder.php';

class AdminBootstrapSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_bootstrap_seeder_restores_non_sensitive_admin_skeleton(): void
    {
        DB::table('admin_menu')->delete();
        DB::table('admin_permissions')->delete();
        DB::table('admin_roles')->delete();
        DB::table('admin_users')->delete();
        DB::table('admin_role_users')->delete();

        $seeder = new \AdminBootstrapSeeder();
        $seeder->run();

        $this->assertSame(22, DB::table('admin_menu')->count());
        $this->assertSame(6, DB::table('admin_permissions')->count());
        $this->assertSame(1, DB::table('admin_roles')->count());
        $this->assertSame(0, DB::table('admin_users')->count());
        $this->assertSame(0, DB::table('admin_role_users')->count());
    }
}
