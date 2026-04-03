<?php

namespace App\Service;

use App\Models\Carmis;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CarmiImportService
{
    public function import(int $goodsId, ?string $carmisList, ?string $carmisTxt, bool $removeDuplication = false): int
    {
        $content = $this->resolveContent($carmisList, $carmisTxt);
        $rows = $this->buildRows($goodsId, $content, $removeDuplication);

        Carmis::query()->insert($rows);

        if (!empty($carmisTxt)) {
            Storage::disk('public')->delete($carmisTxt);
        }

        return count($rows);
    }

    public function buildRows(int $goodsId, string $content, bool $removeDuplication = false): array
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $rows = [];

        foreach (preg_split('/\R/u', $content) as $line) {
            $carmi = trim($line);

            if ($carmi === '') {
                continue;
            }

            $rows[] = [
                'goods_id' => $goodsId,
                'carmi' => $carmi,
                'status' => Carmis::STATUS_UNSOLD,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if ($removeDuplication) {
            $rows = array_values(assoc_unique($rows, 'carmi'));
        }

        return $rows;
    }

    private function resolveContent(?string $carmisList, ?string $carmisTxt): string
    {
        if (!empty($carmisList)) {
            return $carmisList;
        }

        if (!empty($carmisTxt)) {
            return Storage::disk('public')->get($carmisTxt);
        }

        throw new InvalidArgumentException(admin_trans('carmis.rule_messages.carmis_list_and_carmis_txt_can_not_be_empty'));
    }
}
