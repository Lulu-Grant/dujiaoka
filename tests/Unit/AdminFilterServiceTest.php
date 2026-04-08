<?php

namespace Tests\Unit;

use App\Service\AdminFilterService;
use Tests\TestCase;

class AdminFilterServiceTest extends TestCase
{
    public function test_attach_trashed_scope_registers_only_trashed_filter(): void
    {
        $filter = new class {
            public $scopeLabel;
            public $onlyTrashedCalled = false;

            public function scope($label)
            {
                $this->scopeLabel = $label;

                return $this;
            }

            public function onlyTrashed(): void
            {
                $this->onlyTrashedCalled = true;
            }
        };

        app(AdminFilterService::class)->attachTrashedScope($filter);

        $this->assertSame(admin_trans('dujiaoka.trashed'), $filter->scopeLabel);
        $this->assertTrue($filter->onlyTrashedCalled);
    }

    public function test_apply_created_at_range_only_applies_present_bounds(): void
    {
        $query = new class {
            public $calls = [];

            public function where($column, $operator, $value): self
            {
                $this->calls[] = [$column, $operator, $value];

                return $this;
            }
        };

        app(AdminFilterService::class)->applyCreatedAtRange($query, [
            'start' => '2026-04-01 00:00:00',
            'end' => '2026-04-02 23:59:59',
        ]);

        $this->assertSame([
            ['created_at', '>=', '2026-04-01 00:00:00'],
            ['created_at', '<=', '2026-04-02 23:59:59'],
        ], $query->calls);
    }
}
