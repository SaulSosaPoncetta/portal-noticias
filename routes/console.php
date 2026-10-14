<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tareas programadas
|--------------------------------------------------------------------------
| En local:      php artisan schedule:work   (deja la terminal abierta)
| En el VPS:     un cron que ejecute "php artisan schedule:run" cada minuto (Módulo 11)
*/

// Cotizaciones y clima cada 30 minutos
Schedule::command('portal:actualizar-datos')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
