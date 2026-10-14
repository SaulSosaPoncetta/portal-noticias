<?php

namespace Database\Seeders;

use App\Models\Cotizacion;
use Illuminate\Database\Seeder;

/**
 * Monedas del widget. Los códigos coinciden con los de dolarapi.com,
 * que se usa en el Módulo 8 para actualizar los valores automáticamente.
 * Los valores de acá son solo de ejemplo.
 */
class CotizacionSeeder extends Seeder
{
    public function run(): void
    {
        $monedas = [
            // codigo,           nombre,                   compra,  venta
            ['oficial',          'Dólar Oficial',          1485.00, 1535.00],
            ['blue',             'Dólar Blue',             1530.00, 1550.00],
            ['bolsa',            'Dólar MEP',              1530.10, 1538.30],
            ['contadoconliqui',  'Dólar CCL',              1605.20, 1615.89],
            ['tarjeta',          'Dólar Tarjeta',          null,    1995.50],
            ['mayorista',        'Dólar Mayorista',        1436.00, 1439.00],
            ['cripto',           'Dólar Cripto',           1598.00, 1611.82],
            ['eur',              'Euro',                   1630.00, 1688.61],
            ['brl',              'Real',                   285.00,  300.79],
        ];

        foreach ($monedas as $orden => [$codigo, $nombre, $compra, $venta]) {
            Cotizacion::updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre'         => $nombre,
                    'compra'         => $compra,
                    'venta'          => $venta,
                    'actualizada_at' => now(),
                    'orden'          => $orden + 1,
                    'visible'        => true,
                ]
            );
        }
    }
}
