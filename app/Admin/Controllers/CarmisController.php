<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Forms\ImportCarmis;
use App\Admin\Repositories\Carmis;
use App\Service\AdminDetailFieldService;
use App\Service\AdminPageCardService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Carmis as CarmisModel;
use App\Service\AdminFilterService;
use App\Service\AdminGridRestoreActionService;
use App\Service\AdminSelectOptionService;
use App\Service\CatalogAdminPresenterService;

class CarmisController extends AdminController
{


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Carmis(['goods']), function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id')->sortable();
            $grid->column('goods.gd_name', admin_trans('carmis.fields.goods_id'));
            $grid->column('status')->select(CarmisModel::getStatusMap());
            $grid->column('is_loop')->display([app(CatalogAdminPresenterService::class), 'loopLabel']);
            $grid->column('carmi')->limit(20);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('goods_id')->select(app(AdminSelectOptionService::class)->automaticGoodsOptions());
                $filter->equal('status')->select(CarmisModel::getStatusMap());
                app(AdminFilterService::class)->attachTrashedScope($filter);
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $actions->append(new Restore(app(AdminGridRestoreActionService::class)->model(CarmisModel::class)));
                }
            });
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                if (app(AdminGridRestoreActionService::class)->shouldAttach()) {
                    $batch->add(new BatchRestore(app(AdminGridRestoreActionService::class)->model(CarmisModel::class)));
                }
            });
            $grid->export()->titles(['goods.gd_name' => admin_trans('carmis.fields.goods_id'), 'carmi' => admin_trans('carmis.fields.carmi'), 'created_at' => admin_trans('admin.created_at')]);
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
        return Show::make($id, new Carmis(['goods']), function (Show $show) {
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'id',
                'goods.gd_name' => admin_trans('carmis.fields.goods_id'),
            ]);
            $show->field('status')->as([app(CatalogAdminPresenterService::class), 'carmiStatusLabel']);
            $show->field('is_loop')->as([app(CatalogAdminPresenterService::class), 'loopLabel']);
            app(AdminDetailFieldService::class)->attachShowFields($show, [
                'carmi',
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
        return Form::make(new Carmis(), function (Form $form) {
            app(AdminDetailFieldService::class)->attachDisplayFields($form, ['id']);
            $form->select('goods_id')->options(app(AdminSelectOptionService::class)->automaticGoodsOptions())->required();
            $form->radio('status')
                ->options(CarmisModel::getStatusMap())
                ->default(CarmisModel::STATUS_UNSOLD);
            $form->switch('is_loop')->default(false);
            $form->textarea('carmi')->required();
            app(AdminDetailFieldService::class)->attachDisplayFields($form, [
                'created_at',
                'updated_at',
            ]);
        });
    }

    /**
     * 导入卡密
     *
     * @param Content $content
     * @return Content
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function importCarmis(Content $content)
    {
        return app(AdminPageCardService::class)->attach(
            $content,
            admin_trans('carmis.fields.import_carmis'),
            new ImportCarmis()
        );
    }
}
