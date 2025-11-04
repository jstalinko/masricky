<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('infos')->insert([
            [
                'title' => 'Welcome to Our Platform',
                'content' => 'Thank you for joining! Explore features and stay connected.',
                'label' => 'general',
            ],
            [
                'title' => 'System Maintenance',
                'content' => 'We will perform maintenance on Sunday at 2 AM. Expect downtime for 30 minutes.',
                'label' => 'important',
            ],
            [
                'title' => 'New Update Released',
                'content' => 'Version 2.1 is now live! Check out the new dashboard and bug fixes.',
                'label' => 'update',
            ],
        ]);
    }
}
