<?php

namespace App\Service\DataTransferObjects;

use App\Models\Coupon;
use App\Models\Goods;

class CreateOrderData
{
    /**
     * @var Goods
     */
    public $goods;

    /**
     * @var Coupon|null
     */
    public $coupon;

    /**
     * @var string|null
     */
    public $otherIpt;

    /**
     * @var int
     */
    public $buyAmount;

    /**
     * @var int
     */
    public $payID;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string|null
     */
    public $buyIP;

    /**
     * @var string
     */
    public $searchPwd;

    public function __construct(
        Goods $goods,
        ?Coupon $coupon,
        ?string $otherIpt,
        int $buyAmount,
        int $payID,
        string $email,
        ?string $buyIP,
        string $searchPwd = ''
    ) {
        $this->goods = $goods;
        $this->coupon = $coupon;
        $this->otherIpt = $otherIpt;
        $this->buyAmount = $buyAmount;
        $this->payID = $payID;
        $this->email = $email;
        $this->buyIP = $buyIP;
        $this->searchPwd = $searchPwd;
    }
}
