<?php

use Illuminate\Database\Seeder;

require_once __DIR__ . '/BootstrapSeeder.php';

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BootstrapSeeder::class,
        ]);
    }
}
