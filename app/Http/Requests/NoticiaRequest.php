<?php

namespace App\Http\Requests;

use App\Models\Noticia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoticiaRequest extends FormRequest
{
    /** La autorización real se hace en el controlador con la policy. */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cuerpo = (string) $this->input('cuerpo', '');

        // Quill deja "<p><br></p>" cuando el editor está vacío
        $tieneTexto = trim(strip_tags($cuerpo)) !== '';
        $tieneMedia = str_contains($cuerpo, '<img') || str_contains($cuerpo, '<iframe');

        $this->merge([
            'titulo'        => trim(preg_replace('/\s+/', ' ', (string) $this->input('titulo'))),
            'cuerpo'        => ($tieneTexto || $tieneMedia) ? $cuerpo : null,
            'destacada'     => $this->boolean('destacada'),
            'quitar_imagen' => $this->boolean('quitar_imagen'),
            'estado'        => $this->input('estado', Noticia::ESTADO_BORRADOR),
        ]);
    }

    public function rules(): array
    {
        return [
            'categoria_id'     => ['required', Rule::exists('categorias', 'id')],
            'volanta'          => ['nullable', 'string', 'max:80'],
            'titulo'           => ['required', 'string', 'max:200'],
            'bajada'           => ['nullable', 'string', 'max:600'],
            'cuerpo'           => ['required', 'string'],
            'imagen'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'quitar_imagen'    => ['boolean'],
            'epigrafe'         => ['nullable', 'string', 'max:255'],
            'video_url'        => ['nullable', 'url', 'max:255'],
            'tags'             => ['nullable', 'string', 'max:500'],
            'estado'           => ['required', Rule::in([Noticia::ESTADO_BORRADOR, Noticia::ESTADO_PUBLICADA])],
            'publicada_at'     => ['nullable', 'date'],
            'destacada'        => ['boolean'],
            'orden_portada'    => ['nullable', 'integer', 'min:1', 'max:99'],
            'meta_titulo'      => ['nullable', 'string', 'max:255'],
            'meta_descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.required' => 'Elegí la sección.',
            'titulo.required'       => 'El título es obligatorio.',
            'titulo.max'            => 'El título no puede superar los 200 caracteres.',
            'bajada.max'            => 'La bajada no puede superar los 600 caracteres.',
            'cuerpo.required'       => 'La nota no tiene contenido.',
            'imagen.image'          => 'El archivo tiene que ser una imagen.',
            'imagen.mimes'          => 'La imagen tiene que ser JPG, PNG o WEBP.',
            'imagen.max'            => 'La imagen no puede pesar más de 4 MB.',
            'video_url.url'         => 'El link del video no es válido.',
            'orden_portada.max'     => 'La posición en portada va de 1 a 99.',
            'orden_portada.min'     => 'La posición en portada va de 1 a 99.',
        ];
    }
}
