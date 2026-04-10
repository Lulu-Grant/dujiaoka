<?php

namespace App\Service\Contracts;

use App\Service\DataTransferObjects\AdminShellIndexPageData;
use App\Service\DataTransferObjects\AdminShellShowPageData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface AdminShellPageServiceInterface
{
    public function extractFilters(Request $request): array;

    public function paginate(array $filters): LengthAwarePaginator;

    public function find(int $id, ?string $scope = null);

    public function buildIndexPageData(LengthAwarePaginator $records, array $filters): AdminShellIndexPageData;

    public function buildShowPageData($record, ?string $scope = null): AdminShellShowPageData;
}
