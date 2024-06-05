<?php

namespace Database\Seeders;

use App\Models\DetailModule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DetailModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DetailModule::create([
            'client_id' => 1,
            'module_id' => 1,
            'access_id' => 2,
        ]);

        DetailModule::create([
            'client_id' => 1,
            'module_id' => 1,
            'access_id' => 5,
        ]);
    }
}
