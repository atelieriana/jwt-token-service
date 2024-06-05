<?php

namespace Database\Seeders;

use App\Models\Client;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Client::factory()->create([
            'username' => 'yonrismaya2',
            'secret' => sha1(md5('password')),
        ]);
    }
}
