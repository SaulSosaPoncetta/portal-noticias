<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('categorias.gestionar');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug'       => Str::slug($this->input('slug') ?: $this->input('nombre', '')),
            'en_menu'    => $this->boolean('en_menu'),
            'en_portada' => $this->boolean('en_portada'),
            'activa'     => $this->boolean('activa'),
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('categoria')?->id;

        return [
            'nombre'      => ['required', 'string', 'max:80', Rule::unique('categorias', 'nombre')->ignore($id)],
            'slug'        => ['required', 'alpha_dash', 'max:100', Rule::unique('categorias', 'slug')->ignore($id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'color'       => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'en_menu'     => ['boolean'],
            'en_portada'  => ['boolean'],
            'activa'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresá el nombre de la sección.',
            'nombre.unique'   => 'Ya existe una sección con ese nombre.',
            'slug.unique'     => 'Ya existe una sección con esa URL.',
            'slug.required'   => 'La URL no puede quedar vacía.',
            'color.regex'     => 'El color tiene que tener formato #RRGGBB.',
        ];
    }
}
