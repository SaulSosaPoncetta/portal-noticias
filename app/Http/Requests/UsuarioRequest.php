<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('usuarios.gestionar');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
            'email'  => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');

        return [
            'name'     => ['required', 'string', 'max:120'],
            'firma'    => ['nullable', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($usuario?->id)],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'rol'      => ['required', Rule::in(array_keys(User::ROLES))],
            'bio'      => ['nullable', 'string', 'max:500'],
            'activo'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Ingresá el nombre.',
            'email.required'     => 'Ingresá el email.',
            'email.email'        => 'El email no es válido.',
            'email.unique'       => 'Ya hay un usuario con ese email.',
            'password.required'  => 'Ingresá una contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min'       => 'La contraseña tiene que tener al menos 8 caracteres.',
            'password.letters'   => 'La contraseña tiene que tener al menos una letra.',
            'password.numbers'   => 'La contraseña tiene que tener al menos un número.',
            'rol.required'       => 'Elegí un rol.',
        ];
    }
}
