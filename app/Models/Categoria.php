<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'color',
        'orden',
        'en_menu',
        'en_portada',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'orden'      => 'integer',
            'en_menu'    => 'boolean',
            'en_portada' => 'boolean',
            'activa'     => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Genera el slug automáticamente si no se cargó
        static::saving(function (Categoria $categoria) {
            if (blank($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nombre);
            }
        });
    }

    /** Las URLs de sección usan el slug: /seccion/deportes */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true)->orderBy('orden');
    }

    public function scopeDelMenu(Builder $query): Builder
    {
        return $query->activas()->where('en_menu', true);
    }

    public function scopeDePortada(Builder $query): Builder
    {
        return $query->activas()->where('en_portada', true);
    }
}
