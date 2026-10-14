<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles y permisos del panel.
 *
 *  admin    : todo
 *  editor   : todas las noticias (crear, editar, publicar, eliminar), categorías y publicidades
 *  redactor : crea y edita SUS noticias; no puede publicar (quedan en borrador para que las revise un editor)
 */
class RolSeeder extends Seeder
{
    public const PERMISOS = [
        'noticias.ver',
        'noticias.crear',
        'noticias.editar_propias',
        'noticias.editar_todas',
        'noticias.publicar',
        'noticias.eliminar',
        'categorias.gestionar',
        'publicidades.gestionar',
        'cotizaciones.gestionar',
        'configuracion.gestionar',
        'usuarios.gestionar',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISOS as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => User::ROL_ADMIN, 'guard_name' => 'web']);
        $admin->syncPermissions(self::PERMISOS);

        $editor = Role::firstOrCreate(['name' => User::ROL_EDITOR, 'guard_name' => 'web']);
        $editor->syncPermissions([
            'noticias.ver',
            'noticias.crear',
            'noticias.editar_propias',
            'noticias.editar_todas',
            'noticias.publicar',
            'noticias.eliminar',
            'categorias.gestionar',
            'publicidades.gestionar',
        ]);

        $redactor = Role::firstOrCreate(['name' => User::ROL_REDACTOR, 'guard_name' => 'web']);
        $redactor->syncPermissions([
            'noticias.ver',
            'noticias.crear',
            'noticias.editar_propias',
        ]);

        // Asignar roles a los usuarios de ejemplo
        $asignaciones = [
            'admin@portal.test'    => User::ROL_ADMIN,
            'editor@portal.test'   => User::ROL_EDITOR,
            'redactor@portal.test' => User::ROL_REDACTOR,
        ];

        foreach ($asignaciones as $email => $rol) {
            User::where('email', $email)->first()?->syncRoles([$rol]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
