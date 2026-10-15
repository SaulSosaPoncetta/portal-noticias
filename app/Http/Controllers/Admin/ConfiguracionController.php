<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    /** Títulos e íconos de cada grupo, en el orden en que se muestran. */
    public const GRUPOS = [
        'general' => ['Datos del diario',  'newspaper'],
        'redes'   => ['Redes sociales',    'share'],
        'footer'  => ['Datos legales (pie de página)', 'file-earmark-text'],
        'clima'   => ['Clima',             'cloud-sun'],
        'seo'     => ['Google y redes',    'google'],
    ];

    /** Reglas especiales por clave (las demás son texto libre). */
    private const REGLAS = [
        'sitio_nombre'    => ['required', 'string', 'max:80'],
        'sitio_eslogan'   => ['nullable', 'string', 'max:150'],
        'ciudad'          => ['nullable', 'string', 'max:80'],
        'email_contacto'  => ['nullable', 'email', 'max:120'],
        'facebook'        => ['nullable', 'url', 'max:255'],
        'instagram'       => ['nullable', 'url', 'max:255'],
        'youtube'         => ['nullable', 'url', 'max:255'],
        'x_twitter'       => ['nullable', 'url', 'max:255'],
        'whatsapp'        => ['nullable', 'regex:/^\d{8,15}$/'],
        'clima_ciudad'    => ['nullable', 'string', 'max:80'],
        'clima_lat'       => ['nullable', 'numeric', 'between:-90,90'],
        'clima_lon'       => ['nullable', 'numeric', 'between:-180,180'],
        'seo_descripcion' => ['nullable', 'string', 'max:300'],
    ];

    /** Claves que no se editan como texto */
    private const ESPECIALES = ['sitio_logo'];

    public function index(): View
    {
        $configuraciones = Configuracion::orderBy('id')->get()->groupBy('grupo');

        return view('admin.configuracion.index', [
            'configuraciones' => $configuraciones,
            'grupos'          => self::GRUPOS,
            'logo'            => Configuracion::valor('sitio_logo'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $claves = Configuracion::pluck('clave')->diff(self::ESPECIALES);

        // Reglas: las especiales o texto de hasta 1000 caracteres
        $reglas = [
            'logo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:1024'],
            'quitar_logo' => ['nullable', 'boolean'],
        ];
        foreach ($claves as $clave) {
            $reglas["config.{$clave}"] = self::REGLAS[$clave] ?? ['nullable', 'string', 'max:1000'];
        }

        $datos = $request->validate($reglas, [
            'config.sitio_nombre.required' => 'El nombre del diario es obligatorio.',
            'config.*.url'                 => 'El link no es válido (tiene que empezar con https://).',
            'config.whatsapp.regex'        => 'El WhatsApp va solo con números, con código de país. Ej: 5492284123456',
            'config.email_contacto.email'  => 'El email no es válido.',
            'config.clima_lat.*'           => 'La latitud tiene que ser un número entre -90 y 90.',
            'config.clima_lon.*'           => 'La longitud tiene que ser un número entre -180 y 180.',
            'logo.image'                   => 'El logo tiene que ser una imagen.',
            'logo.max'                     => 'El logo no puede pesar más de 1 MB.',
        ]);

        $antes = Configuracion::todas();

        foreach ($claves as $clave) {
            $valor = $datos['config'][$clave] ?? null;
            Configuracion::where('clave', $clave)->update(['valor' => is_string($valor) ? trim($valor) : $valor]);
        }

        // Logo
        if ($request->hasFile('logo')) {
            $this->borrarLogo($antes['sitio_logo'] ?? null);
            $nombre = 'logo-' . Str::random(8) . '.' . strtolower($request->file('logo')->getClientOriginalExtension());
            Configuracion::guardar('sitio_logo', $request->file('logo')->storeAs('sitio', $nombre, 'public'));
        } elseif ($request->boolean('quitar_logo')) {
            $this->borrarLogo($antes['sitio_logo'] ?? null);
            Configuracion::guardar('sitio_logo', null);
        }

        // Los update() masivos no disparan eventos del modelo: limpiamos la caché a mano
        Cache::forget('configuraciones_sitio');

        // Si cambió la ubicación del clima, se vuelve a consultar
        $nuevo = Configuracion::todas();
        if (($antes['clima_lat'] ?? null) !== ($nuevo['clima_lat'] ?? null)
            || ($antes['clima_lon'] ?? null) !== ($nuevo['clima_lon'] ?? null)
            || ($antes['clima_ciudad'] ?? null) !== ($nuevo['clima_ciudad'] ?? null)) {
            Cache::forget('clima_actual');
            Cache::forget('clima_ultimo_bueno');
        }

        return back()->with('ok', 'Configuración guardada.');
    }

    private function borrarLogo(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
