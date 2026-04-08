<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Coupon;
use App\Service\AdminDetailFieldService;
use App\Service\AdminFilterService;
use App\Service\AdminGridRestoreActionService;
use App\Service\AdminStatusPresenterService;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Coupon as CouponModel;
use App\Service\AdminSelectOptionService;
use App\Service\CouponAdminPresenterService;

class CouponController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Coupon(['goods']), function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id')->sortable();
            $grid->column('discount');
            $grid->column('is_use')->select(CouponModel::getStatusUseMap());
            $grid->column('is_open')->switch();
            $grid->column('coupon')->copyable();
            $grid->column('ret');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $actions->append(new Restore(app(AdminGridRestoreActionService::class)->model(CouponModel::class)));
                }
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $batch->add(new BatchRestore(app(AdminGridRestoreActionService::class)->model(CouponModel::class)));
                }
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('goods.goods_id', admin_trans('coupon.fields.goods_id'))->select(app(AdminSelectOptionService::class)->goodsOptions());
                app(AdminFilterService::class)->attachTrashedScope($filter);
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
        return Show::make($id, new Coupon(), function (Show $show) {
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'id',
                'discount',
            ]);
            $show->field('is_use')->as([app(AdminStatusPresenterService::class), 'couponUsageLabel']);
            $show->field('is_open')->as([app(AdminStatusPresenterService::class), 'openStatusLabel']);
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'coupon',
                'ret',
                'created_at',
                'updated_at',
            ]);
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(Coupon::with('goods'), function (Form $form) {
            app(AdminDetailFieldService::class)->attachDisplayFields($form, ['id']);
            $form->multipleSelect('goods', admin_trans('coupon.fields.goods_id'))
                ->options(app(AdminSelectOptionService::class)->goodsOptions())
                ->customFormat(function ($v) {
                    return app(CouponAdminPresenterService::class)->selectedGoodsIds($v);
                });
            $form->currency('discount')->default(0)->required();
            $form->text('coupon')->required();
            $form->number('ret')->default(1);
            $form->radio('is_use')->options(CouponModel::getStatusUseMap())->default(CouponModel::STATUS_UNUSED);
            $form->switch('is_open')->default(CouponModel::STATUS_OPEN);
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'created_at',
                'updated_at',
            ]);
        });
    }
}
