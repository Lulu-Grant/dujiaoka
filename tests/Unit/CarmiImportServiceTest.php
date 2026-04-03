<?php

namespace Tests\Unit;

use App\Models\Carmis;
use App\Service\CarmiImportService;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class CarmiImportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carmis::query()->delete();
    }

    protected function tearDown(): void
    {
        Carmis::query()->delete();

        parent::tearDown();
    }

    public function test_build_rows_can_remove_duplicates(): void
    {
        $rows = app(CarmiImportService::class)->buildRows(1, "alpha\nbeta\nalpha\n", true);

        $this->assertCount(2, $rows);
        $this->assertSame(['alpha', 'beta'], array_column($rows, 'carmi'));
    }

    public function test_import_can_read_uploaded_file_and_delete_it_after_insert(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('imports/carmis.txt', "foo\nbar\n");

        $count = app(CarmiImportService::class)->import(1, null, 'imports/carmis.txt', false);

        $this->assertSame(2, $count);
        $this->assertSame(2, Carmis::query()->count());
        Storage::disk('public')->assertMissing('imports/carmis.txt');
    }

    public function test_import_requires_manual_or_uploaded_content(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CarmiImportService::class)->import(1, null, null, false);
    }
}
