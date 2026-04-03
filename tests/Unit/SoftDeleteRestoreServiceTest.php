<?php

namespace Tests\Unit;

use App\Models\Carmis;
use App\Service\SoftDeleteRestoreService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class SoftDeleteRestoreServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carmis::query()->forceDelete();
    }

    protected function tearDown(): void
    {
        Carmis::query()->forceDelete();

        parent::tearDown();
    }

    public function test_restore_one_restores_soft_deleted_record(): void
    {
        $id = $this->insertDeletedCarmi('RESTORE-ONE');

        $restored = app(SoftDeleteRestoreService::class)->restoreOne(Carmis::class, $id);

        $this->assertTrue($restored);
        $this->assertNull(Carmis::withTrashed()->findOrFail($id)->deleted_at);
    }

    public function test_restore_many_restores_multiple_records(): void
    {
        $ids = [
            $this->insertDeletedCarmi('RESTORE-MANY-1'),
            $this->insertDeletedCarmi('RESTORE-MANY-2'),
        ];

        $count = app(SoftDeleteRestoreService::class)->restoreMany(Carmis::class, $ids);

        $this->assertSame(2, $count);
        $this->assertSame(0, Carmis::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->count());
    }

    public function test_restore_service_rejects_non_model_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid restore model class.');

        app(SoftDeleteRestoreService::class)->restoreOne(\stdClass::class, 1);
    }

    private function insertDeletedCarmi(string $carmi): int
    {
        return (int) DB::table('carmis')->insertGetId([
            'goods_id' => 1,
            'carmi' => $carmi,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => now(),
        ]);
    }
}
