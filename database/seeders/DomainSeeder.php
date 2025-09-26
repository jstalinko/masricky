<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'user_id' => 1,
                'domain' => 'wordpress.test',
                'connected' => true,
            ],
            [
                'user_id' => 1,
                'domain' => 'localhost:8000',
                'connected' => true
            ]
        ];
        foreach($data as $d)
        {
            echo "SUCESS\n";
            Domain::create($d);
        }
    }
}
