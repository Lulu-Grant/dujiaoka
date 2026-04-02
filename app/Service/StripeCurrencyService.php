<?php

namespace App\Service;

use GuzzleHttp\Client;

class StripeCurrencyService
{
    /**
     * 根据 RMB 获取美元金额
     *
     * @param float $cny
     * @return float
     * @throws \Exception
     */
    public function convertCnyToUsd(float $cny): float
    {
        $rates = $this->fetchRates();
        $data = $rates['body']['data'] ?? null;
        if (!isset($data)) {
            throw new \Exception('汇率接口异常');
        }

        $fxRate = 0.13;
        foreach ($data as $item) {
            if (($item['ccyNbr'] ?? null) == '美元') {
                $fxRate = (float) bcdiv(100, $item['rtcOfr'], 2);
                break;
            }
        }

        return (float) bcmul($cny, $fxRate, 2);
    }

    protected function fetchRates(): array
    {
        $client = new Client();
        $response = $client->get('https://m.cmbchina.com/api/rate/fx-rate');

        return json_decode((string) $response->getBody(), true);
    }
}
