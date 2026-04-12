<?php

namespace App\Service;

use App\Models\GoodsGroup;

class GoodsGroupActionService
{
    public function createDefaults(): array
    {
        return [
            'gp_name' => '',
            'is_open' => GoodsGroup::STATUS_OPEN,
            'ord' => 1,
        ];
    }

    public function editDefaults(GoodsGroup $group): array
    {
        return [
            'gp_name' => $group->gp_name,
            'is_open' => $group->is_open,
            'ord' => $group->ord,
        ];
    }

    public function create(array $payload): GoodsGroup
    {
        $group = new GoodsGroup();
        $group->gp_name = $payload['gp_name'];
        $group->is_open = $payload['is_open'];
        $group->ord = $payload['ord'];
        $group->save();

        return $group;
    }

    public function update(GoodsGroup $group, array $payload): GoodsGroup
    {
        $group->gp_name = $payload['gp_name'];
        $group->is_open = $payload['is_open'];
        $group->ord = $payload['ord'];
        $group->save();

        return $group->fresh();
    }
}
