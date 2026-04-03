<?php

namespace App\Service;

class CouponAdminPresenterService
{
    public function selectedGoodsIds($value): array
    {
        if (!$value) {
            return [];
        }

        return array_column($value, 'id');
    }
}
