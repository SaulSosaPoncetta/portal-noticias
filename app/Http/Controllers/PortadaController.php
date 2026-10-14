<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cotizacion;
use App\Models\Noticia;
use App\Models\Publicidad;
use Illuminate\View\View;

class PortadaController extends Controller
{
    public function index(): View
    {
        // 1) Nota principal: la destacada más reciente (o la última publicada si no hay destacadas)
        $principal = Noticia::publicadas()->destacadas()->recientes()->with('categoria')->first()
            ?? Noticia::publicadas()->recientes()->with('categoria')->first();

        // 2) Las que siguen: primero las que tienen posición fija, después por fecha
        $siguientes = Noticia::publicadas()
            ->with('categoria')
            ->when($principal, fn ($q) => $q->where('id', '!=', $principal->id))
            ->orderByRaw('orden_portada IS NULL')
            ->orderBy('orden_portada')
            ->recientes()
            ->take(11)
            ->get();

        $secundarias = $siguientes->take(2);     // al lado de la principal
        $grilla = $siguientes->slice(2);         // grilla de 3 columnas

        // 3) Bloques por sección, sin repetir lo que ya está arriba
        $yaMostradas = $siguientes->pluck('id')->push($principal?->id)->filter();

        $bloques = Categoria::dePortada()->get()
            ->map(function (Categoria $categoria) use ($yaMostradas) {
                $notas = Noticia::publicadas()
                    ->deCategoria($categoria)
                    ->whereNotIn('id', $yaMostradas)
                    ->with('categoria')
                    ->recientes()
                    ->take(4)
                    ->get();

                return compact('categoria', 'notas');
            })
            ->filter(fn ($bloque) => $bloque['notas']->isNotEmpty())
            ->values();

        // 4) Columna lateral
        $ultimas = Noticia::publicadas()->recientes()->take(6)->get(['id', 'titulo', 'slug', 'publicada_at']);
        $masLeidas = Noticia::masLeidas(2)->with('categoria')->take(5)->get();
        $cotizaciones = Cotizacion::visibles()->get();

        // 5) Publicidades por posición
        $publicidades = Publicidad::vigentes()->get()->groupBy('posicion');

        return view('publico.portada', compact(
            'principal', 'secundarias', 'grilla', 'bloques',
            'ultimas', 'masLeidas', 'cotizaciones', 'publicidades'
        ));
    }
}
