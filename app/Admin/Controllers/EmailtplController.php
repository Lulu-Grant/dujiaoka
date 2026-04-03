<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Emailtpl;
use App\Service\AdminFormBehaviorService;
use App\Service\AdminGridRestoreActionService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Emailtpl as EmailTplModel;

class EmailtplController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Emailtpl(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('tpl_name');
            $grid->column('tpl_token');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            $grid->disableViewButton();
            $grid->disableDeleteButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('tpl_name');
                $filter->like('tpl_token');
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $actions->append(new Restore(app(AdminGridRestoreActionService::class)->model(EmailTplModel::class)));
                }
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $batch->add(new BatchRestore(app(AdminGridRestoreActionService::class)->model(EmailTplModel::class)));
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
        return Show::make($id, new Emailtpl(), function (Show $show) {
            $show->field('id');
            $show->field('tpl_name');
            $show->field('tpl_content');
            $show->field('tpl_token');
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
        return Form::make(new Emailtpl(), function (Form $form) {
            $behavior = app(AdminFormBehaviorService::class);
            $tokenMode = $behavior->emailTemplateTokenFieldMode($form->isCreating());

            $form->display('id');
            $form->text('tpl_name')->required();
            $form->editor('tpl_content')->required();
            $tokenField = $form->text('tpl_token');
            if ($tokenMode['required']) {
                $tokenField->required();
            }
            if ($tokenMode['disabled']) {
                $tokenField->disable();
            }
            $form->display('created_at');
            $form->display('updated_at');
            $form->disableViewButton();
            $form->disableDeleteButton();
            $form->footer(function ($footer) {
                app(AdminFormBehaviorService::class)->disableViewCheck($footer);
            });
        });
    }
}
