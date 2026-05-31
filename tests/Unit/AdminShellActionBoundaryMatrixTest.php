<?php

namespace Tests\Unit;

use Tests\TestCase;

class AdminShellActionBoundaryMatrixTest extends TestCase
{
    public function test_boundary_matrix_covers_current_text_batch_actions(): void
    {
        $contents = file_get_contents(base_path('docs/admin-shell-action-boundary-matrix.md'));

        foreach ([
            'batch-buy-prompt',
            'batch-buy-prompt-trim',
            'batch-description',
            'batch-description-trim',
            'batch-keywords',
            'batch-keywords-suffix',
            'batch-keywords-trim',
            'batch-keywords-collapse-spaces',
            'batch-info',
            'batch-title',
            'batch-title-prefix',
            'batch-title-suffix',
            'batch-title-trim',
            'batch-title-collapse-spaces',
            'batch-name',
            'batch-name-prefix',
            'batch-name-suffix',
            'batch-name-replace',
            'batch-name-trim',
            'batch-name-collapse-spaces',
            'batch-code',
            'batch-code-prefix',
            'batch-code-suffix',
            'batch-code-replace',
            'batch-code-trim',
            'batch-code-collapse-spaces',
            'batch-trim',
            'batch-collapse-spaces',
            'batch-replace',
            'batch-suffix',
        ] as $action) {
            $this->assertStringContainsString($action, $contents);
        }
    }

    public function test_boundary_matrix_keeps_forbidden_high_risk_fields_explicit(): void
    {
        $contents = file_get_contents(base_path('docs/admin-shell-action-boundary-matrix.md'));

        foreach ([
            'actual_price',
            'retail_price',
            'in_stock',
            'trade_no',
            'merchant_key',
            'merchant_pem',
            'pay_handleroute',
            'discount',
            'ret',
            'goods_id',
            '库存扣减',
            '履约',
            '通知',
        ] as $boundary) {
            $this->assertStringContainsString($boundary, $contents);
        }
    }
}
