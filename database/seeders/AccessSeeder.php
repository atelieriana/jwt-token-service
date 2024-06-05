<?php

namespace Database\Seeders;

use App\Enum\AccessEnum;
use App\Models\Access;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Access::create([
            'desc' => 'ALL'
        ]);

        Access::create([
            'desc' => 'CREATE'
        ]);

        Access::create([
            'desc' => 'UPDATE'
        ]);

        Access::create([
            'desc' => 'DELETE'
        ]);

        Access::create([
            'desc' => 'READ'
        ]);
    }
}
