<?php

namespace App\Service;

use App\Models\BaseModel;
use App\Models\Coupon;

class AdminStatusPresenterService
{
    public function openStatusLabel($isOpen): string
    {
        return (int) $isOpen === BaseModel::STATUS_OPEN
            ? admin_trans('dujiaoka.status_open')
            : admin_trans('dujiaoka.status_close');
    }

    public function couponUsageLabel($isUse): string
    {
        return (int) $isUse === Coupon::STATUS_UNUSED
            ? admin_trans('coupon.fields.status_unused')
            : admin_trans('coupon.fields.status_use');
    }
}
