<?php

namespace App\Service;

use App\Models\Carmis;
use App\Models\Goods;

class CatalogAdminPresenterService
{
    public function goodsTypeLabel($type): string
    {
        $map = Goods::getGoodsTypeMap();

        return $map[$type] ?? admin_trans('goods.fields.automatic_delivery');
    }

    public function carmiStatusLabel($status): string
    {
        return (int) $status === Carmis::STATUS_UNSOLD
            ? admin_trans('carmis.fields.status_unsold')
            : admin_trans('carmis.fields.status_sold');
    }

    public function loopLabel($isLoop): string
    {
        return (int) $isLoop === 1
            ? admin_trans('carmis.fields.yes')
            : '';
    }
}
