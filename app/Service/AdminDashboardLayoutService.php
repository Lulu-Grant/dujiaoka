<?php

namespace App\Service;

use App\Admin\Charts\DashBoard;
use App\Admin\Charts\PayoutRateCard;
use App\Admin\Charts\SalesCard;
use App\Admin\Charts\SuccessOrderCard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class AdminDashboardLayoutService
{
    public function build(Content $content): Content
    {
        return $content
            ->header(admin_trans('dujiaoka.dashboard'))
            ->description(admin_trans('dujiaoka.dashboard_description'))
            ->body(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->row($this->titleView());
                    $column->row(new DashBoard());
                });

                $row->column(6, function (Column $column) {
                    $column->row(function (Row $row) {
                        $row->column(6, new SuccessOrderCard());
                        $row->column(6, new PayoutRateCard());
                    });

                    $column->row(new SalesCard());
                });
            });
    }

    public function titleView()
    {
        return view('admin.dashboard.title');
    }
}
