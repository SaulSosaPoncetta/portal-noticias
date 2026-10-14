<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RolSeeder::class,
            CategoriaSeeder::class,
            ConfiguracionSeeder::class,
            CotizacionSeeder::class,
            NoticiaSeeder::class,
        ]);
    }
}
