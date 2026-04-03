<?php

namespace App\Service;

use App\Models\Pay;

class PayAdminPresenterService
{
    /**
     * @var AdminStatusPresenterService
     */
    private $statusPresenter;

    public function __construct(AdminStatusPresenterService $statusPresenter)
    {
        $this->statusPresenter = $statusPresenter;
    }

    public function lifecycleBadge(?string $payCheck): string
    {
        $label = Pay::getLifecycleLabel($payCheck);

        if (Pay::isRetiredGateway($payCheck)) {
            return $this->badge('danger', $label);
        }

        if (Pay::isLegacyGateway($payCheck)) {
            return $this->badge('warning', $label);
        }

        return $this->badge('success', $label);
    }

    public function lifecycleLabel(?string $payCheck): string
    {
        return Pay::getLifecycleLabel($payCheck);
    }

    public function clientLabel($payClient): string
    {
        $map = Pay::getClientMap();

        return $map[$payClient] ?? admin_trans('pay.fields.pay_client_pc');
    }

    public function methodLabel($payMethod): string
    {
        $map = Pay::getMethodMap();

        return $map[$payMethod] ?? admin_trans('pay.fields.method_jump');
    }

    public function openStatusLabel($isOpen): string
    {
        return $this->statusPresenter->openStatusLabel($isOpen);
    }

    private function badge(string $variant, string $label): string
    {
        return "<span class='badge badge-{$variant}'>{$label}</span>";
    }
}
