<?php

namespace App\Services;

use App\Models\Cotizacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Actualiza la tabla `cotizaciones` desde dolarapi.com (gratis, sin API key).
 *
 *  - /v1/dolares       → oficial, blue, bolsa (MEP), contadoconliqui, mayorista, cripto, tarjeta
 *  - /v1/cotizaciones  → EUR, BRL, CLP, UYU (se guardan con código en minúscula: eur, brl...)
 *
 * Las monedas que ya existen conservan el nombre, el orden y la visibilidad que se configuraron en el panel.
 * Las que aparecen por primera vez se crean ocultas.
 */
class CotizacionService
{
    private const URL_BASE = 'https://dolarapi.com/v1';

    /** Nombres por defecto para monedas nuevas */
    private const NOMBRES = [
        'oficial'         => 'Dólar Oficial',
        'blue'            => 'Dólar Blue',
        'bolsa'           => 'Dólar MEP',
        'contadoconliqui' => 'Dólar CCL',
        'mayorista'       => 'Dólar Mayorista',
        'cripto'          => 'Dólar Cripto',
        'tarjeta'         => 'Dólar Tarjeta',
        'eur'             => 'Euro',
        'brl'             => 'Real',
        'clp'             => 'Peso Chileno',
        'uyu'             => 'Peso Uruguayo',
    ];

    /**
     * @return int cantidad de cotizaciones actualizadas
     * @throws \RuntimeException si no se pudo obtener ningún dato
     */
    public function actualizar(): int
    {
        $registros = [];

        // Dólares (la "casa" es el código)
        foreach ($this->consultar('/dolares') as $item) {
            $registros[$item['casa']] = $item;
        }

        // Otras monedas (se omite USD porque ya está en /dolares)
        foreach ($this->consultar('/cotizaciones') as $item) {
            if (($item['moneda'] ?? '') !== 'USD') {
                $registros[strtolower($item['moneda'])] = $item;
            }
        }

        if (empty($registros)) {
            throw new \RuntimeException('dolarapi.com no devolvió datos.');
        }

        $siguienteOrden = (int) Cotizacion::max('orden') + 1;

        foreach ($registros as $codigo => $item) {
            $cotizacion = Cotizacion::firstOrNew(['codigo' => $codigo]);

            if (! $cotizacion->exists) {
                $cotizacion->nombre = self::NOMBRES[$codigo] ?? ($item['nombre'] ?? strtoupper($codigo));
                $cotizacion->orden = $siguienteOrden++;
                $cotizacion->visible = false;
            }

            $cotizacion->compra = $item['compra'] ?? null;
            $cotizacion->venta = $item['venta'] ?? null;
            $cotizacion->actualizada_at = isset($item['fechaActualizacion'])
                ? Carbon::parse($item['fechaActualizacion'])->setTimezone(config('app.timezone'))
                : now();

            $cotizacion->save();
        }

        return count($registros);
    }

    private function consultar(string $ruta): array
    {
        try {
            $respuesta = Http::timeout(10)
                ->retry(2, 500, throw: false)
                ->acceptJson()
                ->get(self::URL_BASE . $ruta);

            if ($respuesta->successful() && is_array($respuesta->json())) {
                return $respuesta->json();
            }

            Log::warning("Cotizaciones: {$ruta} respondió HTTP {$respuesta->status()}");
        } catch (Throwable $e) {
            Log::warning("Cotizaciones: error consultando {$ruta}: {$e->getMessage()}");
        }

        return [];
    }
}
