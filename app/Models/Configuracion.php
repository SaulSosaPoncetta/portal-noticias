<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Configuración del sitio (clave/valor).
 *
 * Uso:
 *   Configuracion::valor('sitio_nombre');
 *   Configuracion::valor('whatsapp', 'Sin datos');
 *   Configuracion::guardar('ciudad', 'Olavarría');
 */
class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
        'grupo',
        'descripcion',
    ];

    private const CACHE_KEY = 'configuraciones_sitio';

    protected static function booted(): void
    {
        // Cualquier cambio limpia la caché
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** Todas las configuraciones como array [clave => valor], cacheadas. */
    public static function todas(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::pluck('valor', 'clave')->all();
        });
    }

    public static function valor(string $clave, mixed $porDefecto = null): mixed
    {
        $valor = static::todas()[$clave] ?? null;

        return blank($valor) ? $porDefecto : $valor;
    }

    public static function guardar(string $clave, mixed $valor): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }
}
