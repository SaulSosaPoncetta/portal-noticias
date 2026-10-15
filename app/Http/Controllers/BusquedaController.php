<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusquedaController extends Controller
{
    public function index(Request $request): View
    {
        $texto = trim((string) $request->input('q', ''));
        $texto = mb_substr(preg_replace('/\s+/', ' ', $texto), 0, 100);

        $noticias = null;

        if (mb_strlen($texto) >= 3) {
            // Cada palabra tiene que aparecer en el título, la volanta o la bajada
            $palabras = collect(explode(' ', $texto))
                ->filter(fn ($p) => mb_strlen($p) >= 2)
                ->take(6);

            $noticias = Noticia::publicadas()
                ->with('categoria')
                ->where(function ($q) use ($palabras) {
                    foreach ($palabras as $palabra) {
                        $like = '%' . addcslashes($palabra, '%_\\') . '%';
                        $q->where(fn ($w) => $w->where('titulo', 'like', $like)
                            ->orWhere('volanta', 'like', $like)
                            ->orWhere('bajada', 'like', $like));
                    }
                })
                ->recientes()
                ->paginate(12)
                ->withQueryString();
        }

        return view('publico.busqueda', compact('texto', 'noticias'));
    }
}
