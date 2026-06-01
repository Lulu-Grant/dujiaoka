<?php

namespace Tests\Unit;

use Tests\TestCase;

class CiWorkflowReadinessTest extends TestCase
{
    public function test_ci_workflow_uses_php81_mariadb_and_phpunit_path(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/ci.yml'));

        foreach ([
            'name: CI',
            'workflow_dispatch:',
            'PHPUnit (PHP 8.1)',
            'runs-on: ubuntu-22.04',
            'image: mariadb:10.5',
            'uses: actions/checkout@v6',
            'uses: actions/cache@v5',
            'php-version: 8.1',
            'composer install --no-interaction --prefer-dist',
            './scripts/prepare-test-db',
            'vendor/bin/phpunit',
            'APP_ENV: testing',
            'QUEUE_CONNECTION: sync',
        ] as $required) {
            $this->assertStringContainsString($required, $workflow);
        }
    }

    public function test_ci_workflow_drops_php74_experimental_split_after_laravel8_upgrade(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/ci.yml'));
        $prepareScript = file_get_contents(base_path('scripts/prepare-test-db'));

        foreach ([
            'codex/php81-laravel8-upgrade',
            'php-version: 8.1',
            'extensions: bcmath, ctype, curl, dom, fileinfo, gd, json, mbstring, openssl, pdo_mysql, tokenizer, xml, zip',
            'TEST_PHP_BIN: php',
            'TEST_APP_DB_HOST: 127.0.0.1',
            '${{ runner.os }}-php81-composer-${{ hashFiles(\'composer.lock\') }}',
        ] as $required) {
            $this->assertStringContainsString($required, $workflow);
        }

        $this->assertStringNotContainsString('PHPUnit (PHP 7.4)', $workflow);
        $this->assertStringNotContainsString('php-version: 7.4', $workflow);
        $this->assertStringNotContainsString('PHPUnit (PHP 8.1 experimental)', $workflow);
        $this->assertStringContainsString('TEST_PHP_BIN="${TEST_PHP_BIN:-$ROOT_DIR/scripts/php81-docker}"', $prepareScript);
        $this->assertStringContainsString('TEST_APP_DB_HOST="${TEST_APP_DB_HOST:-host.docker.internal}"', $prepareScript);
        $this->assertStringContainsString('"$TEST_PHP_BIN" artisan migrate:fresh --seed --force --env=testing', $prepareScript);
    }

    public function test_ci_workflow_does_not_restore_install_sql_path(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/ci.yml'));

        foreach ([
            'install.sql',
            'database/sql',
            'mysql <',
            'admin/admin',
            'actions/checkout@v4',
            'actions/cache@v4',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $workflow);
        }
    }
}
