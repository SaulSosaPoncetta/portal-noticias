<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // nombre,                color,     en_menu, en_portada
            ['Información General', '#0b3d91', true,  true],
            ['Política',            '#6a1b9a', true,  true],
            ['Policiales',          '#d62828', true,  true],
            ['Deportes',            '#2a9d8f', true,  true],
            ['Espectáculos',        '#e76f51', true,  true],
            ['Economía',            '#2b9348', true,  true],
            ['Agro',                '#7f5539', true,  false],
            ['Nacionales',          '#264653', true,  false],
            ['Necrológicas',        '#495057', true,  false],
        ];

        foreach ($categorias as $orden => [$nombre, $color, $enMenu, $enPortada]) {
            Categoria::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => "Noticias de {$nombre}",
                    'color'       => $color,
                    'orden'       => $orden + 1,
                    'en_menu'     => $enMenu,
                    'en_portada'  => $enPortada,
                    'activa'      => true,
                ]
            );
        }
    }
}
