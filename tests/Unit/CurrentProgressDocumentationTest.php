<?php

namespace Tests\Unit;

use Tests\TestCase;

class CurrentProgressDocumentationTest extends TestCase
{
    public function test_current_progress_documents_match_stable_and_upgrade_stage(): void
    {
        foreach ($this->currentProgressDocuments() as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            $this->assertStringContainsString('90%', $contents, $relativePath);
            $this->assertStringContainsString('v3.0.0 stable', $contents, $relativePath);
            $this->assertStringContainsString('3.1 / 4.0', $contents, $relativePath);
            $this->assertMatchesRegularExpression('/417 tests[, \/]+4284 assertions/', $contents, $relativePath);
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

    public function test_current_planning_documents_do_not_reintroduce_retired_stripe_or_install_sql_language(): void
    {
        $documents = [
            'docs/parallel-development-plan.md',
            'docs/modernization-roadmap.md',
            'docs/rectification-execution-plan.md',
        ];

        foreach ($documents as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            foreach ([
                '`upgrade-stripe`：优先给支付通道线使用',
                '`stripe-best-practices`：优先给支付通道线使用',
                '`install.sql` is now kept as a legacy reference file',
                'legacy PayPal SDK',
                '后台壳继续扩容与操作页落地',
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
