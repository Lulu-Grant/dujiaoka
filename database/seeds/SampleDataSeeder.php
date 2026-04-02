<?php

use Illuminate\Database\Seeder;

require_once __DIR__ . '/OrderTableSeeder.php';
require_once __DIR__ . '/PaySampleSeeder.php';

class SampleDataSeeder extends Seeder
{
    /**
     * Seed optional sample data for local development only.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PaySampleSeeder::class,
            OrderTableSeeder::class,
        ]);
    }
}
