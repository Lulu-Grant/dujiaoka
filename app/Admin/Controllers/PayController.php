<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Pay;
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
                $filter->scope(admin_trans('dujiaoka.trashed'))->onlyTrashed();
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (request('_scope_') == admin_trans('dujiaoka.trashed')) {
                    $actions->append(new Restore(PayModel::class));
                }
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                if (request('_scope_') == admin_trans('dujiaoka.trashed')) {
                    $batch->add(new BatchRestore(PayModel::class));
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
            $show->field('id');
            $show->field('pay_name');
            $show->field('merchant_id');
            $show->field('merchant_key');
            $show->field('merchant_pem');
            $show->field('pay_check');
            $show->field('pay_check', admin_trans('pay.fields.lifecycle'))->as(function ($payCheck) {
                return app(PayAdminPresenterService::class)->lifecycleLabel($payCheck);
            });
            $show->field('pay_client')->as(function ($payClient) {
                return app(PayAdminPresenterService::class)->clientLabel($payClient);
            });
            $show->field('pay_handleroute');
            $show->field('pay_method')->as(function ($payMethod) {
                return app(PayAdminPresenterService::class)->methodLabel($payMethod);
            });
            $show->field('is_open')->as(function ($isOpen) {
                return app(PayAdminPresenterService::class)->openStatusLabel($isOpen);
            });
            $show->field('created_at');
            $show->field('updated_at');
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
            $form->display('id');
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
            $form->display('created_at');
            $form->display('updated_at');
            $form->disableDeleteButton();
        });
    }
}
