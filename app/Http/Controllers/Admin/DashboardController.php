<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Noticia;
use App\Models\VisitaDiaria;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $usuario = auth()->user();

        // El redactor solo ve sus propias notas en el resumen
        $base = fn () => Noticia::query()
            ->when(! $usuario->can('noticias.editar_todas'), fn ($q) => $q->where('user_id', $usuario->id));

        $resumen = [
            'publicadas_hoy' => $base()->publicadas()->whereDate('publicada_at', today())->count(),
            'borradores'     => $base()->where('estado', Noticia::ESTADO_BORRADOR)->count(),
            'programadas'    => $base()->where('estado', Noticia::ESTADO_PUBLICADA)->where('publicada_at', '>', now())->count(),
            'visitas_hoy'    => VisitaDiaria::whereDate('fecha', today())->sum('cantidad'),
        ];

        $ultimas = $base()
            ->with(['categoria', 'autor'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        $masLeidas = Noticia::masLeidas(2)
            ->with('categoria')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('resumen', 'ultimas', 'masLeidas'));
    }
}
