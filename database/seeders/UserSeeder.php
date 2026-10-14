<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios iniciales del panel.
 * Los roles (admin / editor / redactor) se asignan en el Módulo 4.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'name'  => 'Administrador',
                'firma' => 'Redacción',
                'email' => 'admin@portal.test',
            ],
            [
                'name'  => 'Laura Méndez',
                'firma' => 'Laura Méndez',
                'email' => 'editor@portal.test',
            ],
            [
                'name'  => 'Martín Ríos',
                'firma' => 'Martín Ríos',
                'email' => 'redactor@portal.test',
            ],
        ];

        foreach ($usuarios as $datos) {
            User::updateOrCreate(
                ['email' => $datos['email']],
                $datos + [
                    'password'          => 'admin1234',   // se hashea solo (cast 'hashed')
                    'activo'            => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
