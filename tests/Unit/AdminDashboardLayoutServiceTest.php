<?php

namespace Tests\Unit;

use App\Service\AdminDashboardLayoutService;
use Illuminate\Contracts\View\View;
use Tests\TestCase;

class AdminDashboardLayoutServiceTest extends TestCase
{
    public function test_title_view_returns_dashboard_view(): void
    {
        $view = app(AdminDashboardLayoutService::class)->titleView();

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('admin.dashboard.title', $view->name());
    }
}
