<?php

namespace Tests\Unit;

use Tests\TestCase;

class Composer74ScriptTest extends TestCase
{
    public function test_composer74_prefers_environment_override_then_project_phar_before_system_composer(): void
    {
        $script = file_get_contents(base_path('scripts/composer74'));
        $envPosition = strpos($script, '[ -n "${COMPOSER74_BIN:-}" ]');
        $projectPosition = strpos($script, '[ -f "${PROJECT_COMPOSER_BIN}" ]');
        $homebrewPosition = strpos($script, '[ -f "/opt/homebrew/bin/composer" ]');
        $pathPosition = strpos($script, 'command -v composer');

        $this->assertNotFalse($envPosition);
        $this->assertNotFalse($projectPosition);
        $this->assertNotFalse($homebrewPosition);
        $this->assertNotFalse($pathPosition);
        $this->assertLessThan($projectPosition, $envPosition);
        $this->assertLessThan($homebrewPosition, $projectPosition);
        $this->assertLessThan($pathPosition, $homebrewPosition);
    }

    public function test_project_composer_phar_is_present_for_php74_toolchain(): void
    {
        $composerPath = base_path('tools/composer-2.2.phar');

        $this->assertFileExists($composerPath);
        $this->assertGreaterThan(1000000, filesize($composerPath));
    }
}
