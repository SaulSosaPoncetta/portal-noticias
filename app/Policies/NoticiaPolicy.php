<?php

namespace App\Policies;

use App\Models\Noticia;
use App\Models\User;

/**
 * Quién puede hacer qué con las noticias.
 * Laravel la detecta sola por el nombre (App\Policies\NoticiaPolicy ↔ App\Models\Noticia).
 *
 *  - Admin / Editor: todo sobre cualquier nota.
 *  - Redactor: solo sus notas y solo mientras estén en borrador
 *    (una vez publicada por un editor, ya no la puede tocar).
 */
class NoticiaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('noticias.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('noticias.crear');
    }

    public function update(User $user, Noticia $noticia): bool
    {
        if ($user->can('noticias.editar_todas')) {
            return true;
        }

        return $user->can('noticias.editar_propias')
            && (int) $noticia->user_id === (int) $user->id
            && ($user->can('noticias.publicar') || $noticia->estado === Noticia::ESTADO_BORRADOR);
    }

    public function delete(User $user, Noticia $noticia): bool
    {
        if ($user->can('noticias.eliminar')) {
            return true;
        }

        // El redactor puede borrar sus propios borradores
        return (int) $noticia->user_id === (int) $user->id
            && $noticia->estado === Noticia::ESTADO_BORRADOR;
    }

    public function restore(User $user, Noticia $noticia): bool
    {
        return $user->can('noticias.eliminar');
    }

    public function publish(User $user): bool
    {
        return $user->can('noticias.publicar');
    }
}
