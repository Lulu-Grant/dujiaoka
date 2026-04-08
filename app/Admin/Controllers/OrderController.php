<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Order;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Pay;
use App\Service\AdminDetailFieldService;
use App\Service\AdminFilterService;
use App\Service\AdminGridRestoreActionService;
use App\Service\AdminSelectOptionService;
use App\Service\AdminTextareaPresenterService;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Order as OrderModel;

class OrderController extends AdminController
{


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Order(['goods', 'coupon', 'pay']), function (Grid $grid) {
            $restoreActions = app(AdminGridRestoreActionService::class);
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id')->sortable();
            $grid->column('order_sn')->copyable();
            $grid->column('title');
            $grid->column('type')->using(OrderModel::getTypeMap())
                ->label([
                    OrderModel::AUTOMATIC_DELIVERY => Admin::color()->success(),
                    OrderModel::MANUAL_PROCESSING => Admin::color()->info(),
                ]);
            $grid->column('email')->copyable();
            $grid->column('goods.gd_name', admin_trans('order.fields.goods_id'));
            $grid->column('goods_price');
            $grid->column('buy_amount');
            $grid->column('total_price');
            $grid->column('coupon.coupon', admin_trans('order.fields.coupon_id'));
            $grid->column('coupon_discount_price');
            $grid->column('wholesale_discount_price');
            $grid->column('actual_price');
            $grid->column('pay.pay_name', admin_trans('order.fields.pay_id'));
            $grid->column('buy_ip');
            $grid->column('search_pwd')->copyable();
            $grid->column('trade_no')->copyable();
            $grid->column('status')
                ->select(OrderModel::getStatusMap());
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            $grid->disableCreateButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('order_sn');
                $filter->like('title');
                $filter->equal('status')->select(OrderModel::getStatusMap());
                $filter->equal('email');
                $filter->equal('trade_no');
                $filter->equal('type')->select(OrderModel::getTypeMap());
                $filter->equal('goods_id')->select(app(AdminSelectOptionService::class)->goodsOptions());
                $filter->equal('coupon_id')->select(app(AdminSelectOptionService::class)->couponOptions());
                $filter->equal('pay_id')->select(app(AdminSelectOptionService::class)->payOptions());
                $filter->whereBetween('created_at', function ($q) {
                    app(AdminFilterService::class)->applyCreatedAtRange($q, (array) $this->input);
                })->datetime();
                app(AdminFilterService::class)->attachTrashedScope($filter);
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $restoreActions->attachRowRestore($actions, OrderModel::class);
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                $restoreActions->attachBatchRestore($batch, OrderModel::class);
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Order(['goods', 'coupon', 'pay']), function (Show $show) {
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'id',
                'order_sn',
                'title',
                'email',
                'goods.gd_name' => admin_trans('order.fields.goods_id'),
                'goods_price',
                'buy_amount',
                'coupon.coupon' => admin_trans('order.fields.coupon_id'),
                'coupon_discount_price',
                'wholesale_discount_price',
                'total_price',
                'actual_price',
                'buy_ip',
            ]);
            $show->field('info')->unescape()->as([app(AdminTextareaPresenterService::class), 'render']);
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'pay.pay_name' => admin_trans('order.fields.pay_id'),
            ]);
            $show->field('status')->using(OrderModel::getStatusMap());
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'search_pwd',
                'trade_no',
            ]);
            $show->field('type')->using(OrderModel::getTypeMap());
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'created_at',
                'updated_at',
            ]);
            $show->disableEditButton();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Order(['goods', 'coupon', 'pay']), function (Form $form) {
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'id',
                'order_sn',
            ]);
            $form->text('title');
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'goods.gd_name' => admin_trans('order.fields.goods_id'),
                'goods_price',
                'buy_amount',
                'coupon.coupon' => admin_trans('order.fields.coupon_id'),
                'coupon_discount_price',
                'wholesale_discount_price',
                'total_price',
                'actual_price',
                'email',
            ]);
            $form->textarea('info');
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'buy_ip',
                'pay.pay_name' => admin_trans('order.fields.pay_id'),
            ]);
            $form->radio('status')->options(OrderModel::getStatusMap());
            $form->text('search_pwd');
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'trade_no',
            ]);
            $form->radio('type')->options(OrderModel::getTypeMap());
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'created_at',
                'updated_at',
            ]);
        });
    }
}
