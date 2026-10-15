<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;

/**
 * Datos iniciales para PRODUCCIÓN (sin noticias ni usuarios de ejemplo).
 *
 * Carga roles, secciones, configuración y cotizaciones, y crea el usuario
 * administrador preguntando los datos por consola.
 *
 * Uso en el VPS:
 *   php artisan db:seed --class=ProduccionSeeder --force
 */
class ProduccionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            CategoriaSeeder::class,
            ConfiguracionSeeder::class,
            CotizacionSeeder::class,
        ]);

        if (User::role(User::ROL_ADMIN)->exists()) {
            $this->command->info('Ya existe un administrador: no se crea otro.');
            return;
        }

        $this->command->newLine();
        $this->command->info('Crear el usuario administrador');

        do {
            $datos = [
                'name'     => $this->command->ask('Nombre y apellido'),
                'email'    => mb_strtolower((string) $this->command->ask('Email')),
                'password' => $this->command->secret('Contraseña (mínimo 8, con letras y números)'),
            ];

            $validador = Validator::make($datos, [
                'name'     => ['required', 'max:120'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8', 'regex:/[A-Za-z]/', 'regex:/\d/'],
            ]);

            foreach ($validador->errors()->all() as $error) {
                $this->command->error($error);
            }
        } while ($validador->fails());

        $admin = User::create($datos + [
            'firma'             => 'Redacción',
            'activo'            => true,
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles([User::ROL_ADMIN]);

        $this->command->info("Administrador {$admin->email} creado.");
    }
}
