<?php

namespace App\Service;

use App\Models\Goods;

class GoodsActionService
{
    public function createDefaults(): array
    {
        return [
            'group_id' => null,
            'coupon_ids' => [],
            'gd_name' => '',
            'gd_description' => '',
            'gd_keywords' => '',
            'picture' => '',
            'type' => Goods::AUTOMATIC_DELIVERY,
            'retail_price' => 0,
            'actual_price' => 0,
            'in_stock' => 0,
            'sales_volume' => 0,
            'buy_limit_num' => 0,
            'buy_prompt' => '',
            'description' => '',
            'other_ipu_cnf' => '',
            'wholesale_price_cnf' => '',
            'api_hook' => '',
            'ord' => 1,
            'is_open' => Goods::STATUS_OPEN,
        ];
    }

    public function editDefaults(Goods $goods): array
    {
        return [
            'group_id' => $goods->group_id,
            'coupon_ids' => $goods->coupon->pluck('id')->map(function ($id) {
                return (int) $id;
            })->all(),
            'gd_name' => $goods->gd_name,
            'gd_description' => $goods->gd_description,
            'gd_keywords' => $goods->gd_keywords,
            'picture' => (string) $goods->picture,
            'type' => $goods->type,
            'retail_price' => $goods->retail_price,
            'actual_price' => $goods->actual_price,
            'in_stock' => $goods->getOriginal('in_stock'),
            'sales_volume' => $goods->sales_volume,
            'buy_limit_num' => $goods->buy_limit_num,
            'buy_prompt' => (string) $goods->buy_prompt,
            'description' => (string) $goods->description,
            'other_ipu_cnf' => (string) $goods->other_ipu_cnf,
            'wholesale_price_cnf' => (string) $goods->wholesale_price_cnf,
            'api_hook' => (string) $goods->api_hook,
            'ord' => $goods->ord,
            'is_open' => $goods->is_open,
        ];
    }

    public function create(array $payload): Goods
    {
        $goods = new Goods();
        $this->fill($goods, $payload);
        $goods->save();
        $goods->coupon()->sync($payload['coupon_ids']);

        return $goods->fresh(['group:id,gp_name', 'coupon:id,coupon']);
    }

    public function update(Goods $goods, array $payload): Goods
    {
        $this->fill($goods, $payload);
        $goods->save();
        $goods->coupon()->sync($payload['coupon_ids']);

        return $goods->fresh(['group:id,gp_name', 'coupon:id,coupon']);
    }

    private function fill(Goods $goods, array $payload): void
    {
        $goods->group_id = $payload['group_id'];
        $goods->gd_name = $payload['gd_name'];
        $goods->gd_description = $payload['gd_description'];
        $goods->gd_keywords = $payload['gd_keywords'];
        $goods->picture = $payload['picture'];
        $goods->type = $payload['type'];
        $goods->retail_price = $payload['retail_price'];
        $goods->actual_price = $payload['actual_price'];
        $goods->in_stock = $payload['in_stock'];
        $goods->sales_volume = $payload['sales_volume'];
        $goods->buy_limit_num = $payload['buy_limit_num'];
        $goods->buy_prompt = $payload['buy_prompt'];
        $goods->description = $payload['description'];
        $goods->other_ipu_cnf = $payload['other_ipu_cnf'];
        $goods->wholesale_price_cnf = $payload['wholesale_price_cnf'];
        $goods->api_hook = $payload['api_hook'];
        $goods->ord = $payload['ord'];
        $goods->is_open = $payload['is_open'];
    }
}
