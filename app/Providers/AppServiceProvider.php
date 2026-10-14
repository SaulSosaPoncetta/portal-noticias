<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Noticia;
use App\Services\ClimaService;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fechas en español ("miércoles 14 de octubre", "hace 5 minutos")
        Carbon::setLocale(config('app.locale', 'es'));
        setlocale(LC_TIME, 'es_AR.UTF-8', 'es_ES.UTF-8', 'es');

        // Paginación con estilos de Bootstrap 5
        Paginator::useBootstrapFive();

        // Datos que necesita el layout público en todas las páginas
        View::composer('layouts.publico', function ($view) {
            $view->with([
                'config'         => Configuracion::todas(),
                'categoriasMenu' => Categoria::delMenu()->get(['id', 'nombre', 'slug', 'color']),
                'ultimoMomento'  => Noticia::publicadas()->recientes()->take(6)->get(['id', 'titulo', 'slug', 'publicada_at']),
            ]);
        });

        // Clima para la barra superior y la columna lateral (cacheado 30 minutos)
        View::composer(['layouts.publico', 'publico.partials.sidebar'], function ($view) {
            $view->with('clima', app(ClimaService::class)->obtener());
        });
    }
}
