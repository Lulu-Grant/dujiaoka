<?php

namespace App\Service;

use App\Models\Carmis;

class CarmiActionService
{
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
