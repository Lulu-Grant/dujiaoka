<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpireOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire {--minutes=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire unpaid orders that have passed the configured timeout';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $minutes = (int) ($this->option('minutes') ?: dujiaoka_config_get('order_expire_time', 5));
        if ($minutes <= 0) {
            $minutes = 5;
        }

        $expiredCount = app('Service\OrderService')->expireTimedOutOrders($minutes);

        $this->info("Expired {$expiredCount} order(s).");

        return 0;
    }
}
