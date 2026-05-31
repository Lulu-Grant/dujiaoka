<?php

namespace Tests\Unit;

use Tests\TestCase;

class PaymentSdkBoundaryTest extends TestCase
{
    public function test_paypal_and_stripe_controllers_do_not_import_sdk_namespaces_directly(): void
    {
        foreach (glob(app_path('Http/Controllers/Pay/*.php')) as $controllerPath) {
            $contents = file_get_contents($controllerPath);

            $this->assertStringNotContainsString('use PayPal\\', $contents, $controllerPath);
            $this->assertStringNotContainsString('use Stripe\\', $contents, $controllerPath);
            $this->assertStringNotContainsString('new \\PayPal\\', $contents, $controllerPath);
            $this->assertStringNotContainsString('new \\Stripe\\', $contents, $controllerPath);
        }
    }

    public function test_legacy_paypal_sdk_access_stays_inside_sdk_service(): void
    {
        $allowedPath = app_path('Service/PaypalSdkService.php');

        foreach (glob(app_path('Service/*.php')) as $servicePath) {
            $contents = file_get_contents($servicePath);
            if ($servicePath === $allowedPath) {
                continue;
            }

            $this->assertStringNotContainsString('use PayPal\\', $contents, $servicePath);
            $this->assertStringNotContainsString('new \\PayPal\\', $contents, $servicePath);
        }
    }
}
