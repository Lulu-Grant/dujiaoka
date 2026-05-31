<?php

namespace Tests\Unit;

use Tests\TestCase;

class CurrentProgressDocumentationTest extends TestCase
{
    public function test_current_progress_documents_match_rc_and_stable_ready_stage(): void
    {
        foreach ($this->currentProgressDocuments() as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            $this->assertStringContainsString('90%', $contents, $relativePath);
            $this->assertStringContainsString('RC', $contents, $relativePath);
            $this->assertStringContainsString('stable-ready', $contents, $relativePath);
            $this->assertMatchesRegularExpression('/405 tests[, \/]+2674 assertions/', $contents, $relativePath);
        }
    }

    public function test_current_progress_documents_do_not_revert_to_beta1_expansion_language(): void
    {
        foreach ($this->currentProgressDocuments() as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            foreach ([
                '`82%`',
                '当前 beta.1 收口口径',
                '后台壳继续扩容',
                '后台替换中后期',
                '升级前清障中前期',
                'v3.0.0-beta.1 候选',
            ] as $stalePhrase) {
                $this->assertStringNotContainsString($stalePhrase, $contents, $relativePath);
            }
        }
    }

    private function currentProgressDocuments(): array
    {
        return [
            'docs/current-progress-super-summary.md',
            'docs/current-baseline-audit.md',
            'docs/execution-baseline.md',
        ];
    }
}
