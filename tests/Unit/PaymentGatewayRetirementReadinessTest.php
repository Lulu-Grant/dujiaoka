<?php

namespace Tests\Unit;

use Tests\TestCase;

class PaymentGatewayRetirementReadinessTest extends TestCase
{
    public function test_retired_gateway_routes_and_dependencies_do_not_return(): void
    {
        $routes = file_get_contents(base_path('routes/common/pay.php'));
        $composer = file_get_contents(base_path('composer.json'));
        $lock = file_get_contents(base_path('composer.lock'));

        foreach ([
            'pay/paypal',
            'pay/stripe',
            'pay/coinbase',
            'pay/mapay',
            'pay/tokenpay',
            'pay/payjs',
            'pay/vpay',
            'pay/paysapi',
            'paypal/rest-api-sdk-php',
            'stripe/stripe-php',
        ] as $needle) {
            $this->assertStringNotContainsString($needle, $routes.$composer.$lock);
        }
    }

    public function test_retired_gateways_do_not_return_to_runtime_files(): void
    {
        foreach ($this->runtimeFiles() as $relativePath) {
            $contents = strtolower(file_get_contents(base_path($relativePath)));

            foreach ([
                'paypal',
                'stripe',
                'coinbase',
                'mapay',
                'tokenpay',
                'payjs',
                'vpay',
                'paysapi',
            ] as $retiredGateway) {
                $this->assertStringNotContainsString($retiredGateway, $contents, $relativePath);
            }
        }
    }

    public function test_retired_gateway_implementations_are_removed_from_current_code(): void
    {
        foreach ([
            'app/Http/Controllers/Pay/PaypalPayController.php',
            'app/Http/Controllers/Pay/StripeController.php',
            'app/Http/Controllers/Pay/CoinbaseController.php',
            'app/Http/Controllers/Pay/MapayController.php',
            'app/Http/Controllers/Pay/TokenPayController.php',
            'app/Service/PaypalSdkService.php',
            'app/Service/StripeSdkService.php',
            'resources/views/stripe/checkout.blade.php',
        ] as $relativePath) {
            $this->assertFileDoesNotExist(base_path($relativePath));
        }
    }

    public function test_payment_samples_only_include_maintained_gateway_routes(): void
    {
        $seeder = file_get_contents(base_path('database/seeds/PaySampleSeeder.php'));

        foreach ([
            "'/pay/alipay'",
            "'/pay/wepay'",
            "'/pay/yipay'",
            "'/pay/epusdt'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $seeder);
        }

        foreach ([
            'paypal',
            'stripe',
            'coinbase',
            'mapay',
            'tokenpay',
            'payjs',
            'vpay',
            'paysapi',
        ] as $needle) {
            $this->assertStringNotContainsString($needle, strtolower($seeder));
        }
    }

    private function runtimeFiles(): array
    {
        $basePath = base_path();
        $allowedLifecycleFiles = [
            'app/Models/Pay.php',
        ];
        $paths = [];

        foreach ([
            'app',
            'routes',
            'config',
            'database',
        ] as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($basePath.'/'.$directory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $relativePath = str_replace($basePath.'/', '', $file->getPathname());

                if (
                    $file->isFile()
                    && in_array($file->getExtension(), ['php', 'json'], true)
                    && ! in_array($relativePath, $allowedLifecycleFiles, true)
                ) {
                    $paths[] = $relativePath;
                }
            }
        }

        $paths[] = 'composer.json';

        return $paths;
    }
}
