<?php

use Illuminate\Database\Seeder;

require_once __DIR__ . '/EmailTemplateSeeder.php';
require_once __DIR__ . '/AdminBootstrapSeeder.php';

class BootstrapSeeder extends Seeder
{
    /**
     * Seed bootstrap data required for a fresh install.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminBootstrapSeeder::class,
            EmailTemplateSeeder::class,
        ]);
    }
}
