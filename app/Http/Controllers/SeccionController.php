<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\Tag;
use Illuminate\View\View;

/**
 * Listados públicos: por sección (/seccion/deportes) y por etiqueta (/tema/cultura).
 * Ambos usan la misma vista.
 */
class SeccionController extends Controller
{
    private const POR_PAGINA = 13; // 1 grande + 12 en grilla

    public function seccion(Categoria $categoria): View
    {
        abort_unless($categoria->activa, 404);

        $noticias = Noticia::publicadas()
            ->deCategoria($categoria)
            ->with('categoria')
            ->recientes()
            ->paginate(self::POR_PAGINA);

        return view('publico.listado', [
            'titulo'      => $categoria->nombre,
            'descripcion' => $categoria->descripcion,
            'color'       => $categoria->color,
            'etiqueta'    => 'Sección',
            'noticias'    => $noticias,
        ]);
    }

    public function tema(Tag $tag): View
    {
        $noticias = $tag->noticias()
            ->publicadas()
            ->with('categoria')
            ->recientes()
            ->paginate(self::POR_PAGINA);

        abort_if($noticias->total() === 0, 404);

        return view('publico.listado', [
            'titulo'      => $tag->nombre,
            'descripcion' => "Todas las noticias sobre {$tag->nombre}.",
            'color'       => null,
            'etiqueta'    => 'Tema',
            'noticias'    => $noticias,
        ]);
    }
}
