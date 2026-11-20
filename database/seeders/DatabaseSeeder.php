<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Production-safe starter content: categories, services and the existing
     * portfolio projects. Run DemoSeeder separately for sample testimonials,
     * invoices and shares on a local machine.
     */
    public function run(): void
    {
        $this->call([
            ServiceSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}
