<?php

namespace App\Service;

use Illuminate\Database\QueryException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class InstallationService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function install(array $payload): void
    {
        $this->configureRuntimeConnections($payload);
        $this->assertConnections();
        $this->writeEnvironmentFile($payload);
        $this->bootstrapApplication();
        $this->createAdminAccount(
            (string) $payload['admin_username'],
            (string) $payload['admin_password']
        );
        $this->writeInstallLock();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function configureRuntimeConnections(array $payload): void
    {
        $dbConfig = config('database');
        $dbConfig['connections']['mysql'] = array_merge($dbConfig['connections']['mysql'], [
            'host' => $payload['db_host'],
            'port' => $payload['db_port'],
            'database' => $payload['db_database'],
            'username' => $payload['db_username'],
            'password' => $payload['db_password'],
        ]);
        $dbConfig['redis']['default'] = array_merge($dbConfig['redis']['default'], [
            'host' => $payload['redis_host'],
            'password' => $payload['redis_password'] ?? 'null',
            'port' => $payload['redis_port'],
        ]);

        config(['database' => $dbConfig]);
        DB::purge();
        DB::reconnect();
    }

    /**
     * @throws \RedisException
     * @throws QueryException
     */
    private function assertConnections(): void
    {
        DB::connection()->select('select 1 limit 1');
        Redis::set('dujiaoka_com', 'ok');
        Redis::get('dujiaoka_com');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function writeEnvironmentFile(array $payload): void
    {
        $envExamplePath = base_path('.env.example');
        $envPath = base_path('.env');
        $envTemplate = file_get_contents($envExamplePath);
        $payload['app_key'] = 'base64:' . base64_encode(
            Encrypter::generateKey(config('app.cipher'))
        );

        foreach ($payload as $key => $value) {
            $envTemplate = str_replace('{' . $key . '}', (string) $value, $envTemplate);
        }

        file_put_contents($envPath, $envTemplate);
    }

    private function bootstrapApplication(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    }

    public function createAdminAccount(string $username, string $password): void
    {
        $now = now();

        DB::table('admin_users')->updateOrInsert(
            ['username' => $username],
            [
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'avatar' => null,
                'remember_token' => Str::random(60),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $adminUserId = DB::table('admin_users')->where('username', $username)->value('id');
        if (!$adminUserId) {
            return;
        }

        DB::table('admin_role_users')->updateOrInsert(
            ['role_id' => 1, 'user_id' => $adminUserId],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    private function writeInstallLock(): void
    {
        file_put_contents(base_path('install.lock'), 'install ok');
    }
}
