<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public const ROL_ADMIN    = 'admin';
    public const ROL_EDITOR   = 'editor';
    public const ROL_REDACTOR = 'redactor';

    public const ROLES = [
        self::ROL_ADMIN    => 'Administrador',
        self::ROL_EDITOR   => 'Editor',
        self::ROL_REDACTOR => 'Redactor',
    ];

    protected $fillable = [
        'name',
        'firma',
        'email',
        'avatar',
        'bio',
        'activo',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
        ];
    }

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class);
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    /** Nombre que se muestra como autor de la nota. */
    public function getNombreFirmaAttribute(): string
    {
        return $this->firma ?: $this->name;
    }

    /** Nombre legible del rol principal (para mostrar en el panel). */
    public function getRolNombreAttribute(): string
    {
        $rol = $this->getRoleNames()->first();

        return self::ROLES[$rol] ?? 'Sin rol';
    }

    public function getIniciales(): string
    {
        return collect(explode(' ', $this->name))
            ->filter()
            ->take(2)
            ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');
    }
}
