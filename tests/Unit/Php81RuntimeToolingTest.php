<?php

namespace Tests\Unit;

use Tests\TestCase;

class Php81RuntimeToolingTest extends TestCase
{
    public function test_php81_native_scripts_reject_non_php81_fallbacks(): void
    {
        $phpScript = file_get_contents(base_path('scripts/php81'));
        $composerScript = file_get_contents(base_path('scripts/composer81'));

        $this->assertStringContainsString('scripts/php81 requires PHP 8.1', $phpScript);
        $this->assertStringContainsString('scripts/composer81 requires PHP 8.1', $composerScript);
        $this->assertStringContainsString('Use scripts/php81-docker', $phpScript);
        $this->assertStringContainsString('Use scripts/composer81-docker', $composerScript);
    }

    public function test_php81_docker_tooling_pins_runtime_and_required_extensions(): void
    {
        $dockerfile = file_get_contents(base_path('docker/php81-cli.Dockerfile'));
        $script = file_get_contents(base_path('scripts/php81-docker'));
        $serverScript = file_get_contents(base_path('scripts/serve-php81-docker'));
        $phpunit = file_get_contents(base_path('phpunit.php81.xml'));

        $this->assertStringContainsString('FROM php:8.1-cli', $dockerfile);
        $this->assertStringContainsString('docker-php-ext-install -j"$(nproc)" bcmath gd pdo_mysql zip', $dockerfile);
        $this->assertStringContainsString('dujiaoshuka-php81-cli', $script);
        $this->assertStringContainsString('host.docker.internal', $script);
        $this->assertStringContainsString('PHP81_DOCKER_PORT:-8031', $serverScript);
        $this->assertStringContainsString('php -S "0.0.0.0:${PORT}" -t public', $serverScript);
        $this->assertStringContainsString('<server name="DB_HOST" value="host.docker.internal"/>', $phpunit);
        $this->assertStringContainsString('<server name="DB_SOCKET" value=""/>', $phpunit);
    }
}
