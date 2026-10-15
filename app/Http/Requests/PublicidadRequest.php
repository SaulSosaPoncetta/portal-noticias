<?php

namespace App\Http\Requests;

use App\Models\Publicidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('publicidades.gestionar');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activa'              => $this->boolean('activa'),
            'quitar_imagen_mobile' => $this->boolean('quitar_imagen_mobile'),
            'orden'               => $this->input('orden', 0) ?: 0,
        ]);
    }

    public function rules(): array
    {
        $esNueva = ! $this->route('publicidad');

        return [
            'nombre'               => ['required', 'string', 'max:120'],
            'posicion'             => ['required', Rule::in(array_keys(Publicidad::POSICIONES))],
            'imagen'               => [$esNueva ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
            'imagen_mobile'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'quitar_imagen_mobile' => ['boolean'],
            'url'                  => ['nullable', 'url', 'max:255'],
            'desde'                => ['nullable', 'date'],
            'hasta'                => ['nullable', 'date', 'after_or_equal:desde'],
            'orden'                => ['integer', 'min:0', 'max:999'],
            'activa'               => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'      => 'Ingresá el nombre del anunciante.',
            'posicion.required'    => 'Elegí dónde se muestra.',
            'imagen.required'      => 'Subí la imagen del banner.',
            'imagen.image'         => 'El archivo tiene que ser una imagen.',
            'imagen.mimes'         => 'La imagen tiene que ser JPG, PNG, WEBP o GIF.',
            'imagen.max'           => 'La imagen no puede pesar más de 3 MB.',
            'imagen_mobile.max'    => 'La imagen para celular no puede pesar más de 2 MB.',
            'url.url'              => 'El link no es válido (tiene que empezar con https://).',
            'hasta.after_or_equal' => 'La fecha de fin tiene que ser igual o posterior a la de inicio.',
        ];
    }
}
