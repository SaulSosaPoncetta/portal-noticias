<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
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
    }
}
