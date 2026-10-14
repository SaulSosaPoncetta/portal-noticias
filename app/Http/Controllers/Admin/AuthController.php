<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function mostrarLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Ingresá tu email.',
            'email.email'       => 'El email no es válido.',
            'password.required' => 'Ingresá tu contraseña.',
        ]);

        // Solo usuarios activos
        $credenciales['activo'] = true;

        if (! Auth::attempt($credenciales, $request->boolean('recordar'))) {
            throw ValidationException::withMessages([
                'email' => 'Email o contraseña incorrectos, o el usuario está desactivado.',
            ]);
        }

        // Un usuario sin rol no puede entrar al panel
        if (Auth::user()->roles()->doesntExist()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Tu usuario no tiene permisos para acceder al panel.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('ok', 'Cerraste sesión correctamente.');
    }
}
