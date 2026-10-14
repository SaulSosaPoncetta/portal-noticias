<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Noticia extends Model
{
    use SoftDeletes;

    protected $table = 'noticias';

    protected $fillable = [
        'categoria_id',
        'user_id',
        'volanta',
        'titulo',
        'slug',
        'bajada',
        'cuerpo',
        'imagen',
        'epigrafe',
        'video_url',
        'estado',
        'publicada_at',
        'destacada',
        'orden_portada',
        'meta_titulo',
        'meta_descripcion',
    ];

    protected function casts(): array
    {
        return [
            'publicada_at'  => 'datetime',
            'destacada'     => 'boolean',
            'orden_portada' => 'integer',
            'visitas'       => 'integer',
        ];
    }

    public const ESTADO_BORRADOR  = 'borrador';
    public const ESTADO_PUBLICADA = 'publicada';

    protected static function booted(): void
    {
        // Slug único generado a partir del título
        static::saving(function (Noticia $noticia) {
            if (blank($noticia->slug)) {
                $noticia->slug = static::generarSlugUnico($noticia->titulo, $noticia->id);
            }
        });
    }

    public static function generarSlugUnico(string $titulo, ?int $ignorarId = null): string
    {
        $base = Str::limit(Str::slug($titulo), 200, '');
        $slug = $base;
        $i = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /** Las URLs de nota usan el slug: /nota/el-titulo-de-la-nota */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'noticia_tag');
    }

    public function visitasDiarias(): HasMany
    {
        return $this->hasMany(VisitaDiaria::class);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /** Notas visibles en el sitio: publicadas y con fecha ya cumplida. */
    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PUBLICADA)
            ->whereNotNull('publicada_at')
            ->where('publicada_at', '<=', now());
    }

    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderByDesc('publicada_at');
    }

    public function scopeDestacadas(Builder $query): Builder
    {
        return $query->where('destacada', true);
    }

    public function scopeDeCategoria(Builder $query, Categoria|int $categoria): Builder
    {
        $id = $categoria instanceof Categoria ? $categoria->id : $categoria;

        return $query->where('categoria_id', $id);
    }

    /** Más leídas de los últimos $dias días (según visitas_diarias). */
    public function scopeMasLeidas(Builder $query, int $dias = 2): Builder
    {
        $desde = now()->subDays($dias - 1)->toDateString();

        return $query->publicadas()
            ->select('noticias.*')
            ->selectSub(
                VisitaDiaria::selectRaw('COALESCE(SUM(cantidad), 0)')
                    ->whereColumn('visitas_diarias.noticia_id', 'noticias.id')
                    ->where('fecha', '>=', $desde),
                'visitas_periodo'
            )
            ->orderByDesc('visitas_periodo')
            ->orderByDesc('publicada_at');
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    /** URL pública de la imagen (acepta ruta de storage o URL externa). */
    public function getUrlImagenAttribute(): string
    {
        if (blank($this->imagen)) {
            return asset('img/sin-imagen.svg');
        }

        if (Str::startsWith($this->imagen, ['http://', 'https://'])) {
            return $this->imagen;
        }

        return Storage::disk('public')->url($this->imagen);
    }

    public function getEstaProgramadaAttribute(): bool
    {
        return $this->estado === self::ESTADO_PUBLICADA
            && $this->publicada_at
            && $this->publicada_at->isFuture();
    }

    // ---------------------------------------------------------------
    // Acciones
    // ---------------------------------------------------------------

    /** Suma una visita al total y al registro del día. */
    public function registrarVisita(): void
    {
        $this->increment('visitas');

        DB::table('visitas_diarias')->upsert(
            [['noticia_id' => $this->id, 'fecha' => now()->toDateString(), 'cantidad' => 1]],
            ['noticia_id', 'fecha'],
            ['cantidad' => DB::raw('cantidad + 1')]
        );
    }
}
