<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'codigo',
        'nombre',
        'compra',
        'venta',
        'actualizada_at',
        'orden',
        'visible',
    ];

    protected function casts(): array
    {
        return [
            'compra'         => 'decimal:2',
            'venta'          => 'decimal:2',
            'actualizada_at' => 'datetime',
            'orden'          => 'integer',
            'visible'        => 'boolean',
        ];
    }

    public function scopeVisibles(Builder $query): Builder
    {
        return $query->where('visible', true)->orderBy('orden');
    }

    /** Formato argentino: $1.535,00 */
    public static function formatear(?string $valor): string
    {
        return $valor === null ? '-' : '$' . number_format((float) $valor, 2, ',', '.');
    }
}
