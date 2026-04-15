<?php

namespace App\Service;

use App\Models\Carmis;

class CarmiActionService
{
    public function parseCarmiIds(string $idsText): array
    {
        $tokens = preg_split('/[\s,，]+/u', trim($idsText), -1, PREG_SPLIT_NO_EMPTY);

        if ($tokens === false) {
            return [];
        }

        $parsed = [];

        foreach ($tokens as $token) {
            if (!ctype_digit($token)) {
                continue;
            }

            $id = (int) $token;

            if ($id > 0) {
                $parsed[$id] = $id;
            }
        }

        return array_values($parsed);
    }

    public function batchLoopDefaults(array $carmiIds = []): array
    {
        return [
            'carmi_ids' => $carmiIds,
            'ids_text' => implode("\n", $carmiIds),
            'is_loop' => 1,
        ];
    }

    public function batchLoopContext(array $carmiIds): array
    {
        $carmis = Carmis::query()
            ->with('goods:id,gd_name')
            ->whereIn('id', $carmiIds)
            ->orderBy('id')
            ->get(['id', 'goods_id', 'status', 'is_loop', 'carmi', 'updated_at']);

        $matchedIds = $carmis->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $missingIds = array_values(array_diff($carmiIds, $matchedIds));

        return [
            'requestedCount' => count($carmiIds),
            'matchedCount' => $carmis->count(),
            'missingCount' => count($missingIds),
            'matchedIds' => $matchedIds,
            'missingIds' => $missingIds,
            'items' => $carmis->map(function (Carmis $carmi) {
                return [
                    'id' => $carmi->id,
                    'goods' => optional($carmi->goods)->gd_name ?: '未关联商品',
                    'status' => Carmis::getStatusMap()[$carmi->status] ?? (string) $carmi->status,
                    'is_loop' => (int) $carmi->is_loop === 1 ? '是' : '否',
                    'carmi' => $carmi->carmi,
                    'updated_at' => (string) $carmi->updated_at,
                ];
            })->all(),
        ];
    }

    public function updateLoopStatus(array $carmiIds, int $isLoop): int
    {
        if (empty($carmiIds)) {
            return 0;
        }

        return Carmis::query()
            ->whereIn('id', $carmiIds)
            ->update([
                'is_loop' => $isLoop,
                'updated_at' => now(),
            ]);
    }

    public function createDefaults(): array
    {
        return [
            'goods_id' => null,
            'status' => Carmis::STATUS_UNSOLD,
            'is_loop' => 0,
            'carmi' => '',
        ];
    }

    public function editDefaults(Carmis $carmi): array
    {
        return [
            'goods_id' => $carmi->goods_id,
            'status' => $carmi->status,
            'is_loop' => $carmi->is_loop,
            'carmi' => $carmi->carmi,
        ];
    }

    public function create(array $payload): Carmis
    {
        $carmi = new Carmis();
        $this->fill($carmi, $payload);
        $carmi->save();

        return $carmi->fresh('goods');
    }

    public function update(Carmis $carmi, array $payload): Carmis
    {
        $this->fill($carmi, $payload);
        $carmi->save();

        return $carmi->fresh('goods');
    }

    private function fill(Carmis $carmi, array $payload): void
    {
        $carmi->goods_id = $payload['goods_id'];
        $carmi->status = $payload['status'];
        $carmi->is_loop = $payload['is_loop'];
        $carmi->carmi = $payload['carmi'];
    }
}
