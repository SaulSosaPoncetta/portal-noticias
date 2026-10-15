<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with('roles')
            ->withCount('noticias')
            ->orderByDesc('activo')
            ->orderBy('name')
            ->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $usuario = new User(['activo' => true]);

        return view('admin.usuarios.form', ['usuario' => $usuario, 'rolActual' => User::ROL_REDACTOR]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $usuario = User::create($request->safe()->except(['rol', 'password_confirmation']) + [
                'email_verified_at' => now(),
            ]);
            $usuario->syncRoles([$request->input('rol')]);
        });

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario creado.');
    }

    public function edit(User $usuario): View
    {
        return view('admin.usuarios.form', [
            'usuario'   => $usuario,
            'rolActual' => $usuario->getRoleNames()->first(),
        ]);
    }

    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        $esUnoMismo = $usuario->is($request->user());
        $datos = $request->safe()->except(['rol', 'password_confirmation']);

        // Sin contraseña nueva = se mantiene la actual
        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        }

        // Uno mismo no puede desactivarse ni sacarse el rol de admin (para no quedar afuera del panel)
        if ($esUnoMismo) {
            $datos['activo'] = true;
        }

        if (! $esUnoMismo && $usuario->hasRole(User::ROL_ADMIN)
            && $request->input('rol') !== User::ROL_ADMIN && $this->cantidadAdminsActivos() <= 1) {
            return back()->withInput()->with('error', 'Tiene que quedar al menos un administrador activo.');
        }

        DB::transaction(function () use ($usuario, $datos, $request, $esUnoMismo) {
            $usuario->update($datos);

            if (! $esUnoMismo) {
                $usuario->syncRoles([$request->input('rol')]);
            }
        });

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    /**
     * Si tiene notas, no se borra (se perdería la firma): se desactiva.
     */
    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->is(auth()->user())) {
            return back()->with('error', 'No podés eliminar tu propio usuario.');
        }

        if ($usuario->hasRole(User::ROL_ADMIN) && $this->cantidadAdminsActivos() <= 1) {
            return back()->with('error', 'Tiene que quedar al menos un administrador activo.');
        }

        if ($usuario->noticias()->withTrashed()->exists()) {
            $usuario->update(['activo' => false]);

            return back()->with('ok', "{$usuario->name} tiene notas publicadas, así que se desactivó en lugar de eliminarse.");
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario eliminado.');
    }

    private function cantidadAdminsActivos(): int
    {
        return User::role(User::ROL_ADMIN)->where('activo', true)->count();
    }
}
