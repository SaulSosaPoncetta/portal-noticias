<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $config = [
            // clave, valor, grupo, descripción
            ['sitio_nombre',     'Diario Horizonte',                         'general', 'Nombre del diario'],
            ['sitio_eslogan',    'Noticias de la ciudad y la región',        'general', 'Frase debajo del logo'],
            ['sitio_logo',       null,                                       'general', 'Logo (si está vacío se muestra el nombre)'],
            ['ciudad',           'Ciudad Ejemplo',                           'general', 'Ciudad principal del diario'],
            ['email_contacto',   'redaccion@diariohorizonte.test',           'general', 'Email de contacto'],

            ['facebook',         'https://www.facebook.com/',                'redes',   'URL de Facebook'],
            ['instagram',        'https://www.instagram.com/',               'redes',   'URL de Instagram'],
            ['youtube',          'https://www.youtube.com/',                 'redes',   'URL de YouTube'],
            ['x_twitter',        'https://x.com/',                           'redes',   'URL de X (Twitter)'],
            ['whatsapp',         '5492284000000',                            'redes',   'WhatsApp (código de país, sin + ni espacios)'],

            ['propietario',      'Horizonte Medios',                         'footer',  'Razón social / propietario'],
            ['director',         'Nombre del Director',                      'footer',  'Director periodístico'],
            ['direccion',        'Av. Principal 1234, Ciudad Ejemplo',       'footer',  'Domicilio legal'],
            ['registro_dnda',    'En trámite',                               'footer',  'N° de registro DNDA'],

            ['clima_ciudad',     'Olavarría',                                'clima',   'Ciudad que se muestra en el widget de clima'],
            ['clima_lat',        '-36.8927',                                 'clima',   'Latitud para el pronóstico'],
            ['clima_lon',        '-60.3225',                                 'clima',   'Longitud para el pronóstico'],

            ['seo_descripcion',  'Noticias de último momento de la ciudad, la región y el país.', 'seo', 'Descripción para Google y redes'],
        ];

        foreach ($config as [$clave, $valor, $grupo, $descripcion]) {
            Configuracion::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor, 'grupo' => $grupo, 'descripcion' => $descripcion]
            );
        }
    }
}
