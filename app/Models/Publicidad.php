<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Publicidad extends Model
{
    protected $table = 'publicidades';

    protected $fillable = [
        'nombre',
        'posicion',
        'imagen',
        'imagen_mobile',
        'url',
        'desde',
        'hasta',
        'orden',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'desde'  => 'date',
            'hasta'  => 'date',
            'orden'  => 'integer',
            'clics'  => 'integer',
            'activa' => 'boolean',
        ];
    }

    public const POSICIONES = [
        'header'        => 'Header (debajo del menú)',
        'sidebar'       => 'Columna lateral',
        'entre_bloques' => 'Entre secciones de portada',
        'nota'          => 'Dentro de la nota',
        'footer'        => 'Arriba del pie de página',
    ];

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /** Activas y dentro del rango de fechas (si lo tienen). */
    public function scopeVigentes(Builder $query): Builder
    {
        $hoy = now()->toDateString();

        return $query->where('activa', true)
            ->where(fn ($q) => $q->whereNull('desde')->orWhere('desde', '<=', $hoy))
            ->where(fn ($q) => $q->whereNull('hasta')->orWhere('hasta', '>=', $hoy))
            ->orderBy('orden');
    }

    public function scopeEnPosicion(Builder $query, string $posicion): Builder
    {
        return $query->where('posicion', $posicion);
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    public function getUrlImagenAttribute(): string
    {
        return $this->resolverUrl($this->imagen);
    }

    public function getUrlImagenMobileAttribute(): ?string
    {
        return $this->imagen_mobile ? $this->resolverUrl($this->imagen_mobile) : null;
    }

    private function resolverUrl(string $ruta): string
    {
        return Str::startsWith($ruta, ['http://', 'https://'])
            ? $ruta
            : Storage::disk('public')->url($ruta);
    }
}
