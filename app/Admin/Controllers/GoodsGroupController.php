<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\GoodsGroup;
use App\Service\AdminDetailFieldService;
use App\Service\AdminFilterService;
use App\Service\AdminFormBehaviorService;
use App\Service\AdminGridRestoreActionService;
use App\Service\AdminStatusPresenterService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\GoodsGroup as GoodsGroupModel;

class GoodsGroupController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new GoodsGroup(), function (Grid $grid) {
            $restoreActions = app(AdminGridRestoreActionService::class);
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id')->sortable();
            $grid->column('gp_name')->editable();
            $grid->column('is_open')->switch();
            $grid->column('ord')->editable();
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            $grid->disableViewButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                app(AdminFilterService::class)->attachTrashedScope($filter);
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $restoreActions->attachRowRestore($actions, GoodsGroupModel::class);
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                $restoreActions->attachBatchRestore($batch, GoodsGroupModel::class);
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
        return Show::make($id, new GoodsGroup(), function (Show $show) {
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'id',
                'gp_name',
            ]);
            $show->field('is_open')->as([app(AdminStatusPresenterService::class), 'openStatusLabel']);
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'ord',
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
        return Form::make(new GoodsGroup(), function (Form $form) {
            app(AdminDetailFieldService::class)->attachDisplayFields($form, ['id']);
            $form->text('gp_name');
            $form->switch('is_open')->default(GoodsGroupModel::STATUS_OPEN);
            $form->number('ord')->default(1)->help(admin_trans('dujiaoka.ord'));
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'created_at',
                'updated_at',
            ]);
            $form->disableViewButton();
            $form->footer(function ($footer) {
                app(AdminFormBehaviorService::class)->disableViewCheck($footer);
            });
        });
    }
}
