<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Pay extends BaseModel
{

    use SoftDeletes;

    protected $table = 'pays';

    /**
     * 跳转
     */
    const METHOD_JUMP = 1;

    /**
     * 扫码
     */
    const METHOD_SCAN = 2;

    /**
     * 电脑
     */
    const PAY_CLIENT_PC = 1;

    /**
     * 手机
     */
    const PAY_CLIENT_MOBILE = 2;

    /**
     * 通用
     */
    const PAY_CLIENT_ALL = 3;

    /**
     * 新版本已退役、不再维护的支付通道
     */
    const RETIRED_GATEWAYS = [
        'paysapi',
        'vpay',
        'payjs',
    ];

    /**
     * 新版本仍保留，但属于后续优先替换的遗留支付通道
     */
    const LEGACY_GATEWAYS = [
        'paypal',
        'stripe',
    ];

    public static function isRetiredGateway(?string $payCheck): bool
    {
        return in_array(strtolower((string) $payCheck), self::RETIRED_GATEWAYS, true);
    }

    public static function isLegacyGateway(?string $payCheck): bool
    {
        return in_array(strtolower((string) $payCheck), self::LEGACY_GATEWAYS, true);
    }

    public static function getLifecycleLabel(?string $payCheck): string
    {
        if (self::isRetiredGateway($payCheck)) {
            return admin_trans('pay.fields.lifecycle_retired');
        }

        if (self::isLegacyGateway($payCheck)) {
            return admin_trans('pay.fields.lifecycle_legacy');
        }

        return admin_trans('pay.fields.lifecycle_active');
    }

    public function getLifecycleAttribute(): ?string
    {
        return $this->pay_check;
    }

    public static function getMethodMap()
    {
        return [
            self::METHOD_JUMP => admin_trans('pay.fields.method_jump'),
            self::METHOD_SCAN => admin_trans('pay.fields.method_scan'),
        ];
    }

    public static function getClientMap()
    {
        return [
            self::PAY_CLIENT_PC => admin_trans('pay.fields.pay_client_pc'),
            self::PAY_CLIENT_MOBILE => admin_trans('pay.fields.pay_client_mobile'),
            self::PAY_CLIENT_ALL => admin_trans('pay.fields.pay_client_all'),
        ];
    }

}
