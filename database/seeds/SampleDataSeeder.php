<?php

use Illuminate\Database\Seeder;

require_once __DIR__ . '/OrderTableSeeder.php';

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
            OrderTableSeeder::class,
        ]);
    }
}
