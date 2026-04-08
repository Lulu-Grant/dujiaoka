<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Pay;
use App\Service\AdminDetailFieldService;
use App\Service\AdminFilterService;
use App\Service\AdminGridRestoreActionService;
use App\Service\PayAdminPresenterService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Pay as PayModel;

class PayController extends AdminController
{


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Pay(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('pay_name');
            $grid->column('pay_check');
            $grid->column('lifecycle', admin_trans('pay.fields.lifecycle'))->display(function () {
                return app(PayAdminPresenterService::class)->lifecycleBadge($this->pay_check);
            });
            $grid->column('pay_method')->select(PayModel::getMethodMap());
            $grid->column('merchant_id')->limit(20);
            $grid->column('merchant_key')->limit(20);
            $grid->column('merchant_pem')->limit(20);
            $grid->column('pay_client')->select(PayModel::getClientMap());
            $grid->column('pay_handleroute');
            $grid->column('is_open')->switch();
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            $grid->disableDeleteButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('pay_check');
                $filter->like('pay_name');
                app(AdminFilterService::class)->attachTrashedScope($filter);
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $actions->append(new Restore(app(AdminGridRestoreActionService::class)->model(PayModel::class)));
                }
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $batch->add(new BatchRestore(app(AdminGridRestoreActionService::class)->model(PayModel::class)));
                }
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
        return Show::make($id, new Pay(), function (Show $show) {
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'id',
                'pay_name',
                'merchant_id',
                'merchant_key',
                'merchant_pem',
                'pay_check',
            ]);
            $show->field('pay_check', admin_trans('pay.fields.lifecycle'))->as([app(PayAdminPresenterService::class), 'lifecycleLabel']);
            $show->field('pay_client')->as([app(PayAdminPresenterService::class), 'clientLabel']);
            $show->field('pay_handleroute');
            $show->field('pay_method')->as([app(PayAdminPresenterService::class), 'methodLabel']);
            $show->field('is_open')->as([app(PayAdminPresenterService::class), 'openStatusLabel']);
            app(AdminDetailFieldService::class)->attachShowFields($show, [
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
        return Form::make(new Pay(), function (Form $form) {
            app(AdminDetailFieldService::class)->attachDisplayFields($form, ['id']);
            $form->text('pay_name')->required();
            $form->text('merchant_id')->required();
            $form->textarea('merchant_key');
            $form->textarea('merchant_pem')->required();
            $form->text('pay_check')->required()
                ->help(admin_trans('pay.fields.pay_check_help'));
            $form->radio('pay_client')
                ->options(PayModel::getClientMap())
                ->default(PayModel::PAY_CLIENT_PC)
                ->required();
            $form->radio('pay_method')
                ->options(PayModel::getMethodMap())
                ->default(PayModel::METHOD_JUMP)
                ->required();
            $form->text('pay_handleroute')->required();
            $form->switch('is_open')->default(PayModel::STATUS_OPEN);
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'created_at',
                'updated_at',
            ]);
            $form->disableDeleteButton();
        });
    }
}
