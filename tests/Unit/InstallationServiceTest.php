<?php

namespace Tests\Unit;

use App\Service\InstallationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallationServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_admin_account_creates_user_and_role_binding(): void
    {
        DB::table('admin_users')->delete();
        DB::table('admin_role_users')->delete();

        app(InstallationService::class)->createAdminAccount('installer-admin', 'super-secret-pass');

        $admin = DB::table('admin_users')->where('username', 'installer-admin')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('super-secret-pass', $admin->password));
        $this->assertSame(1, DB::table('admin_role_users')->where('user_id', $admin->id)->where('role_id', 1)->count());
    }
}
