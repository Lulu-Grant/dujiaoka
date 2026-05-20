<?php

namespace Tests\Unit;

use App\Service\CarmiActionService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CarmiActionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::table('carmis')->whereIn('id', [96201, 96202])->delete();
        DB::table('goods')->whereIn('id', [96201])->delete();

        parent::tearDown();
    }

    public function test_batch_trim_defaults_only_require_ids_for_safe_review(): void
    {
        $defaults = app(CarmiActionService::class)->batchTrimDefaults([96201, 96202]);

        $this->assertSame([96201, 96202], $defaults['carmi_ids']);
        $this->assertSame("96201\n96202", $defaults['ids_text']);
        $this->assertArrayNotHasKey('carmi', $defaults);
    }

    public function test_batch_collapse_spaces_defaults_only_require_ids_for_safe_review(): void
    {
        $defaults = app(CarmiActionService::class)->batchCollapseSpacesDefaults([96201, 96202]);

        $this->assertSame([96201, 96202], $defaults['carmi_ids']);
        $this->assertSame("96201\n96202", $defaults['ids_text']);
        $this->assertArrayNotHasKey('carmi', $defaults);
    }

    public function test_trim_carmis_only_updates_changed_carmi_content(): void
    {
        $this->seedCarmis();

        $affected = app(CarmiActionService::class)->trimCarmis([96201, 96202]);

        $this->assertSame(1, $affected);
        $this->assertSame('CARD-TRIM-001', DB::table('carmis')->where('id', 96201)->value('carmi'));
        $this->assertSame('CARD-PLAIN-002', DB::table('carmis')->where('id', 96202)->value('carmi'));
        $this->assertSame(1, (int) DB::table('carmis')->where('id', 96201)->value('status'));
        $this->assertSame(1, (int) DB::table('carmis')->where('id', 96201)->value('is_loop'));
        $this->assertSame(96201, (int) DB::table('carmis')->where('id', 96201)->value('goods_id'));
    }

    public function test_collapse_carmi_spaces_only_updates_changed_carmi_content(): void
    {
        $this->seedCarmis();

        DB::table('carmis')->where('id', 96201)->update(['carmi' => 'CARD  TRIM　001']);

        $affected = app(CarmiActionService::class)->collapseCarmiSpaces([96201, 96202]);

        $this->assertSame(1, $affected);
        $this->assertSame('CARD TRIM 001', DB::table('carmis')->where('id', 96201)->value('carmi'));
        $this->assertSame('CARD-PLAIN-002', DB::table('carmis')->where('id', 96202)->value('carmi'));
        $this->assertSame(1, (int) DB::table('carmis')->where('id', 96201)->value('status'));
        $this->assertSame(1, (int) DB::table('carmis')->where('id', 96201)->value('is_loop'));
        $this->assertSame(96201, (int) DB::table('carmis')->where('id', 96201)->value('goods_id'));
    }

    private function seedCarmis(): void
    {
        DB::table('goods')->insert([
            'id' => 96201,
            'group_id' => 1,
            'gd_name' => '卡密动作商品',
            'gd_description' => 'desc',
            'gd_keywords' => 'key',
            'picture' => null,
            'retail_price' => 10,
            'actual_price' => 10,
            'in_stock' => 0,
            'sales_volume' => 0,
            'ord' => 1,
            'buy_limit_num' => 0,
            'buy_prompt' => null,
            'description' => 'inst',
            'type' => 1,
            'wholesale_price_cnf' => null,
            'other_ipu_cnf' => null,
            'api_hook' => null,
            'is_open' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('carmis')->insert([
            [
                'id' => 96201,
                'goods_id' => 96201,
                'status' => 1,
                'is_loop' => 1,
                'carmi' => '  CARD-TRIM-001  ',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 96202,
                'goods_id' => 96201,
                'status' => 2,
                'is_loop' => 0,
                'carmi' => 'CARD-PLAIN-002',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
