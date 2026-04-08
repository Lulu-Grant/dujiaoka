<?php

namespace Tests\Unit;

use App\Service\AdminDetailFieldService;
use Tests\TestCase;

class AdminDetailFieldServiceTest extends TestCase
{
    public function test_attach_show_fields_supports_plain_and_labeled_entries(): void
    {
        $show = new class {
            public $fields = [];

            public function field($name, $label = null): void
            {
                $this->fields[] = [$name, $label];
            }
        };

        app(AdminDetailFieldService::class)->attachShowFields($show, [
            'id',
            'goods.gd_name' => admin_trans('order.fields.goods_id'),
        ]);

        $this->assertSame([
            ['id', null],
            ['goods.gd_name', admin_trans('order.fields.goods_id')],
        ], $show->fields);
    }

    public function test_attach_display_fields_supports_plain_and_labeled_entries(): void
    {
        $form = new class {
            public $fields = [];

            public function display($name, $label = null): void
            {
                $this->fields[] = [$name, $label];
            }
        };

        app(AdminDetailFieldService::class)->attachDisplayFields($form, [
            'id',
            'pay.pay_name' => admin_trans('order.fields.pay_id'),
        ]);

        $this->assertSame([
            ['id', null],
            ['pay.pay_name', admin_trans('order.fields.pay_id')],
        ], $form->fields);
    }
}
