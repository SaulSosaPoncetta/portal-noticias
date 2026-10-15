<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\CotizacionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NoticiaController;
use App\Http\Controllers\Admin\PublicidadController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PortadaController;
use App\Http\Controllers\SeccionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
*/

Route::get('/', [PortadaController::class, 'index'])->name('inicio');

Route::get('nota/{noticia}', [NotaController::class, 'show'])->name('nota');
Route::get('seccion/{categoria}', [SeccionController::class, 'seccion'])->name('seccion');
Route::get('tema/{tag}', [SeccionController::class, 'tema'])->name('tema');
Route::get('buscar', [BusquedaController::class, 'index'])->middleware('throttle:30,1')->name('buscar');

// Clic en un banner: suma el clic y redirige al anunciante
Route::get('publicidad/{publicidad}/clic', [PublicidadController::class, 'clic'])
    ->middleware('throttle:60,1')
    ->name('publicidad.clic');

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

        // ---- Módulo 8: Cotizaciones y clima ----
        Route::middleware('permission:cotizaciones.gestionar')->group(function () {
            Route::get('cotizaciones', [CotizacionController::class, 'index'])->name('cotizaciones.index');
            Route::put('cotizaciones', [CotizacionController::class, 'update'])->name('cotizaciones.update');
            Route::post('cotizaciones/actualizar', [CotizacionController::class, 'actualizar'])
                ->middleware('throttle:6,1')
                ->name('cotizaciones.actualizar');
        });

        // ---- Módulo 10: Publicidades ----
        Route::middleware('permission:publicidades.gestionar')->group(function () {
            Route::resource('publicidades', PublicidadController::class)
                ->except('show')
                ->parameters(['publicidades' => 'publicidad']);
        });

        // ---- Módulo 10: Configuración del sitio ----
        Route::middleware('permission:configuracion.gestionar')->group(function () {
            Route::get('configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
            Route::put('configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        });

        // ---- Módulo 10: Usuarios ----
        Route::middleware('permission:usuarios.gestionar')->group(function () {
            Route::resource('usuarios', UsuarioController::class)
                ->except('show')
                ->parameters(['usuarios' => 'usuario']);
        });
    });
});
