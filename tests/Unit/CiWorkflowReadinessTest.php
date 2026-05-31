<?php

namespace Tests\Unit;

use Tests\TestCase;

class CiWorkflowReadinessTest extends TestCase
{
    public function test_ci_workflow_keeps_php74_mariadb_and_phpunit_path(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/ci.yml'));

        foreach ([
            'name: CI',
            'workflow_dispatch:',
            'PHPUnit (PHP 7.4)',
            'runs-on: ubuntu-22.04',
            'image: mariadb:10.5',
            'uses: actions/checkout@v6',
            'uses: actions/cache@v5',
            'php-version: 7.4',
            'composer install --no-interaction --prefer-dist',
            './scripts/prepare-test-db',
            'vendor/bin/phpunit',
            'APP_ENV: testing',
            'QUEUE_CONNECTION: sync',
        ] as $required) {
            $this->assertStringContainsString($required, $workflow);
        }
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
