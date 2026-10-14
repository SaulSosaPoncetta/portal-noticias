<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaDiaria extends Model
{
    protected $table = 'visitas_diarias';

    public $timestamps = false;

    protected $fillable = [
        'noticia_id',
        'fecha',
        'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'fecha'    => 'date',
            'cantidad' => 'integer',
        ];
    }

    public function noticia(): BelongsTo
    {
        return $this->belongsTo(Noticia::class);
    }
}
