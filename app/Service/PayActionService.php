<?php

namespace App\Service;

use App\Models\Pay;

class PayActionService
{
    public function createDefaults(): array
    {
        return [
            'pay_name' => '',
            'merchant_id' => '',
            'merchant_key' => '',
            'merchant_pem' => '',
            'pay_check' => '',
            'pay_client' => Pay::PAY_CLIENT_PC,
            'pay_method' => Pay::METHOD_JUMP,
            'pay_handleroute' => '',
            'is_open' => Pay::STATUS_OPEN,
        ];
    }

    public function editDefaults(Pay $pay): array
    {
        return [
            'pay_name' => $pay->pay_name,
            'merchant_id' => $pay->merchant_id,
            'merchant_key' => $pay->merchant_key,
            'merchant_pem' => $pay->merchant_pem,
            'pay_check' => $pay->pay_check,
            'pay_client' => $pay->pay_client,
            'pay_method' => $pay->pay_method,
            'pay_handleroute' => $pay->pay_handleroute,
            'is_open' => $pay->is_open,
        ];
    }

    public function create(array $payload): Pay
    {
        $pay = new Pay();
        $this->apply($pay, $payload);
        $pay->save();

        return $pay;
    }

    public function update(Pay $pay, array $payload): Pay
    {
        $this->apply($pay, $payload);
        $pay->save();

        return $pay->fresh();
    }

    private function apply(Pay $pay, array $payload): void
    {
        $pay->pay_name = $payload['pay_name'];
        $pay->merchant_id = $payload['merchant_id'];
        $pay->merchant_key = $payload['merchant_key'] ?? '';
        $pay->merchant_pem = $payload['merchant_pem'];
        $pay->pay_check = $payload['pay_check'];
        $pay->pay_client = $payload['pay_client'];
        $pay->pay_method = $payload['pay_method'];
        $pay->pay_handleroute = $payload['pay_handleroute'];
        $pay->is_open = $payload['is_open'];
    }
}
