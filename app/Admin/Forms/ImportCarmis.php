<?php

namespace App\Admin\Forms;

use App\Service\AdminSelectOptionService;
use App\Service\CarmiImportService;
use Dcat\Admin\Widgets\Form;
use InvalidArgumentException;

class ImportCarmis extends Form
{

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        try {
            app(CarmiImportService::class)->import(
                (int) $input['goods_id'],
                $input['carmis_list'] ?? null,
                $input['carmis_txt'] ?? null,
                (int) ($input['remove_duplication'] ?? 0) === 1
            );
        } catch (InvalidArgumentException $exception) {
            return $this->response()->error($exception->getMessage());
        }

        return $this
				->response()
				->success(admin_trans('carmis.rule_messages.import_carmis_success'))
				->location('/carmis');
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->confirm(admin_trans('carmis.fields.are_you_import_sure'));
        $this->select('goods_id')->options(app(AdminSelectOptionService::class)->automaticGoodsOptions())->required();
        $this->textarea('carmis_list')
            ->rows(20)
            ->help(admin_trans('carmis.helps.carmis_list'));
        $this->file('carmis_txt')
            ->disk('public')
            ->uniqueName()
            ->accept('txt')
            ->maxSize(5120)
            ->help(admin_trans('carmis.helps.carmis_list'));
        $this->switch('remove_duplication');
    }

}
