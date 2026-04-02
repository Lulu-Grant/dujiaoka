<?php

namespace App\Service\DataTransferObjects;

use Illuminate\Http\Request;

class StripeRequestData
{
    /**
     * @var string
     */
    public $orderSN;

    /**
     * @var string|null
     */
    public $sourceId;

    /**
     * @var string|null
     */
    public $stripeToken;

    public function __construct(string $orderSN, ?string $sourceId = null, ?string $stripeToken = null)
    {
        $this->orderSN = $orderSN;
        $this->sourceId = $sourceId;
        $this->stripeToken = $stripeToken;
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            (string) $request->input('orderid', ''),
            $request->input('source'),
            $request->input('stripeToken')
        );
    }
}
