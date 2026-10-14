<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NoticiaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
| La portada real se arma en el Módulo 7.
*/

Route::get('/', function () {
    return view('welcome');
})->name('inicio');

/*
|--------------------------------------------------------------------------
| Panel de administración
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // Login (solo invitados)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'mostrarLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')   // máximo 5 intentos por minuto
            ->name('login.post');
    });

    // Panel (usuarios logueados con algún rol)
    Route::middleware(['auth', 'role:admin|editor|redactor'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // ---- Módulo 5: Secciones ----
        Route::middleware('permission:categorias.gestionar')->group(function () {
            Route::patch('categorias/{categoria}/mover/{direccion}', [CategoriaController::class, 'mover'])
                ->whereIn('direccion', ['arriba', 'abajo'])
                ->name('categorias.mover');
            Route::resource('categorias', CategoriaController::class)
                ->except('show')
                ->parameters(['categorias' => 'categoria']);
        });

        // ---- Módulo 6: Noticias ----
        Route::middleware('permission:noticias.ver')->group(function () {
            Route::post('noticias/subir-imagen', [NoticiaController::class, 'subirImagen'])->name('noticias.subir-imagen');
            Route::patch('noticias/{id}/restaurar', [NoticiaController::class, 'restaurar'])
                ->whereNumber('id')
                ->name('noticias.restaurar');
            Route::resource('noticias', NoticiaController::class)
                ->except('show')
                ->parameters(['noticias' => 'noticia']);
        });
    });
});
