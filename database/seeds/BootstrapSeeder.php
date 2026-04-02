<?php

use Illuminate\Database\Seeder;

require_once __DIR__ . '/EmailTemplateSeeder.php';

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
            EmailTemplateSeeder::class,
        ]);
    }
}
