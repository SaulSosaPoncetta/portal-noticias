<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'nombre',
        'slug',
    ];

    protected static function booted(): void
    {
        static::saving(function (Tag $tag) {
            if (blank($tag->slug)) {
                $tag->slug = Str::slug($tag->nombre);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function noticias(): BelongsToMany
    {
        return $this->belongsToMany(Noticia::class, 'noticia_tag');
    }

    /** Busca el tag por nombre o lo crea (útil al cargar tags desde el formulario). */
    public static function obtenerOCrear(string $nombre): self
    {
        $nombre = trim($nombre);

        return static::firstOrCreate(
            ['slug' => Str::slug($nombre)],
            ['nombre' => $nombre]
        );
    }
}
