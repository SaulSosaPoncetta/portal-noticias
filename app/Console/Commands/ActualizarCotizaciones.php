<?php

namespace App\Console\Commands;

use App\Services\ClimaService;
use App\Services\CotizacionService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Actualiza cotizaciones y clima.
 * Se ejecuta solo cada 30 minutos (ver routes/console.php) o a mano:
 *   php artisan portal:actualizar-datos
 */
class ActualizarCotizaciones extends Command
{
    protected $signature = 'portal:actualizar-datos';

    protected $description = 'Actualiza las cotizaciones (dolarapi.com) y el clima (Open-Meteo)';

    public function handle(CotizacionService $cotizaciones, ClimaService $clima): int
    {
        $resultado = self::SUCCESS;

        try {
            $cantidad = $cotizaciones->actualizar();
            $this->info("Cotizaciones actualizadas: {$cantidad}");
        } catch (Throwable $e) {
            $this->error('Cotizaciones: ' . $e->getMessage());
            $resultado = self::FAILURE;
        }

        $datos = $clima->refrescar();
        if ($datos) {
            $this->info("Clima: {$datos['ciudad']} {$datos['temperatura']}°C, {$datos['descripcion']}");
        } else {
            $this->error('Clima: no se pudo obtener (revisá clima_lat y clima_lon en la configuración).');
            $resultado = self::FAILURE;
        }

        return $resultado;
    }
}
