<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */

namespace App\Admin\Charts;


use App\Models\Order;
use App\Service\AdminDashboardMetricsService;
use Dcat\Admin\Widgets\Metrics\RadialBar;
use Illuminate\Http\Request;

class DashBoard extends RadialBar
{

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title(admin_trans('dujiaoka.sales_data'));
        $this->height(400);
        $this->chartHeight(300);
        $this->chartLabels(admin_trans('dujiaoka.order_success_rate'));
        $this->dropdown([
            'today' => admin_trans('dujiaoka.last_today'),
            'seven' => admin_trans('dujiaoka.last_seven_days'),
            'month' => admin_trans('dujiaoka.last_month'),
            'year' => admin_trans('dujiaoka.last_year'),
        ]);
    }

    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $summary = app(AdminDashboardMetricsService::class)
            ->successRateSummary((string) $request->get('option', 'today'));

        // 订单数
        $this->withOrderCount($summary['order_count']);
        // 卡片底部
        $this->withFooter(
            $summary['status_totals']['pending'],
            $summary['status_totals']['processing'],
            $summary['status_totals']['completed'],
            $summary['status_totals']['failure'],
            $summary['status_totals']['abnormal']
        );
        // 图表数据
        $this->withChart($summary['success_rate']);
    }

    /**
     * 订单总数
     *
     * @param $count
     * @return DashBoard
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function withOrderCount($count)
    {
        $title = admin_trans('dujiaoka.order_count_number');
        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    <h1 class="font-lg-2 mt-2 mb-0">{$count}</h1>
    <small>{$title}</small>
</div>
HTML
        );
    }

    /**
     * 成交率.
     *
     * @param int $data
     *
     * @return $this
     */
    public function withChart(int $data)
    {
        return $this->chart([
            'series' => [$data],
        ]);
    }

    /**
     * @param $pending
     * @param $processing
     * @param $completed
     * @param $failure
     * @param $abnormal
     * @return DashBoard
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function withFooter($pending, $processing, $completed, $failure, $abnormal)
    {
        $statusPendingTitle = admin_trans('dujiaoka.status_pending_number');
        $statusProcessingNumber = admin_trans('dujiaoka.status_processing_number');
        $statusCompletedNumber = admin_trans('dujiaoka.status_completed_number');
        $statusFailureNumber = admin_trans('dujiaoka.status_failure_number');
        $statusAbnormalNumber = admin_trans('dujiaoka.status_abnormal_number');
        return $this->footer(
            <<<HTML
<div class="d-flex justify-content-between p-1" style="padding-top: 0!important;">
    <div class="text-center">
        <p>{$statusPendingTitle}</p>
        <span class="font-lg-1">{$pending}</span>
    </div>
    <div class="text-center">
        <p>{$statusProcessingNumber}</p>
        <span class="font-lg-1">{$processing}</span>
    </div>
    <div class="text-center">
        <p>{$statusCompletedNumber}</p>
        <span class="font-lg-1">{$completed}</span>
    </div>
    <div class="text-center">
        <p>{$statusFailureNumber}</p>
        <span class="font-lg-1">{$failure}</span>
    </div>
    <div class="text-center">
        <p>{$statusAbnormalNumber}</p>
        <span class="font-lg-1">{$abnormal}</span>
    </div>
</div>
HTML
        );
    }
}
