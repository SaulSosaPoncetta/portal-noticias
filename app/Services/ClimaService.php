<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Clima actual y pronóstico de 3 días desde Open-Meteo (gratis, sin API key).
 *
 * La ciudad y las coordenadas salen de la configuración del sitio:
 * clima_ciudad, clima_lat, clima_lon.
 *
 * Se cachea 30 minutos. Si la API falla, se muestra el último dato bueno que se haya guardado.
 */
class ClimaService
{
    private const URL = 'https://api.open-meteo.com/v1/forecast';
    private const MINUTOS_CACHE = 30;

    /**
     * Códigos WMO → [descripción, ícono de Bootstrap Icons de día, ícono de noche]
     * https://open-meteo.com/en/docs (sección "WMO Weather interpretation codes")
     */
    private const CODIGOS = [
        0  => ['Despejado',                 'sun',               'moon-stars'],
        1  => ['Mayormente despejado',      'sun',               'moon-stars'],
        2  => ['Parcialmente nublado',      'cloud-sun',         'cloud-moon'],
        3  => ['Nublado',                   'clouds',            'clouds'],
        45 => ['Niebla',                    'cloud-fog2',        'cloud-fog2'],
        48 => ['Niebla con escarcha',       'cloud-fog2',        'cloud-fog2'],
        51 => ['Llovizna débil',            'cloud-drizzle',     'cloud-drizzle'],
        53 => ['Llovizna',                  'cloud-drizzle',     'cloud-drizzle'],
        55 => ['Llovizna intensa',          'cloud-drizzle',     'cloud-drizzle'],
        56 => ['Llovizna helada',           'cloud-sleet',       'cloud-sleet'],
        57 => ['Llovizna helada intensa',   'cloud-sleet',       'cloud-sleet'],
        61 => ['Lluvia débil',              'cloud-rain',        'cloud-rain'],
        63 => ['Lluvia',                    'cloud-rain',        'cloud-rain'],
        65 => ['Lluvia intensa',            'cloud-rain-heavy',  'cloud-rain-heavy'],
        66 => ['Lluvia helada',             'cloud-sleet',       'cloud-sleet'],
        67 => ['Lluvia helada intensa',     'cloud-sleet',       'cloud-sleet'],
        71 => ['Nevada débil',              'cloud-snow',        'cloud-snow'],
        73 => ['Nevada',                    'cloud-snow',        'cloud-snow'],
        75 => ['Nevada intensa',            'snow',              'snow'],
        77 => ['Granizo fino',              'cloud-hail',        'cloud-hail'],
        80 => ['Chaparrones débiles',       'cloud-rain',        'cloud-rain'],
        81 => ['Chaparrones',               'cloud-rain-heavy',  'cloud-rain-heavy'],
        82 => ['Chaparrones fuertes',       'cloud-rain-heavy',  'cloud-rain-heavy'],
        85 => ['Chaparrones de nieve',      'cloud-snow',        'cloud-snow'],
        86 => ['Chaparrones de nieve fuertes', 'snow',           'snow'],
        95 => ['Tormenta',                  'cloud-lightning-rain', 'cloud-lightning-rain'],
        96 => ['Tormenta con granizo',      'cloud-hail',        'cloud-hail'],
        99 => ['Tormenta con granizo fuerte', 'cloud-hail',      'cloud-hail'],
    ];

    /** Datos del clima o null si nunca se pudo obtener. */
    public function obtener(): ?array
    {
        $enCache = Cache::get('clima_actual');

        // false = la API falló hace poco y no hay dato viejo: no reintentar hasta que venza
        if ($enCache !== null) {
            return $enCache ?: null;
        }

        return $this->refrescar();
    }

    /**
     * Consulta la API y guarda el resultado.
     * Si falla, devuelve el último dato bueno y vuelve a intentar en 5 minutos.
     */
    public function refrescar(): ?array
    {
        $datos = $this->consultar();

        if ($datos !== null) {
            Cache::put('clima_actual', $datos, now()->addMinutes(self::MINUTOS_CACHE));
            Cache::forever('clima_ultimo_bueno', $datos);

            return $datos;
        }

        $ultimo = Cache::get('clima_ultimo_bueno');
        Cache::put('clima_actual', $ultimo ?? false, now()->addMinutes(5));

        return $ultimo;
    }

    private function consultar(): ?array
    {
        $lat = Configuracion::valor('clima_lat');
        $lon = Configuracion::valor('clima_lon');

        if (! is_numeric($lat) || ! is_numeric($lon)) {
            return null;
        }

        try {
            $respuesta = Http::timeout(4)->acceptJson()->get(self::URL, [
                'latitude'      => $lat,
                'longitude'     => $lon,
                'current'       => 'temperature_2m,apparent_temperature,relative_humidity_2m,wind_speed_10m,weather_code,is_day',
                'daily'         => 'weather_code,temperature_2m_max,temperature_2m_min',
                'timezone'      => config('app.timezone'),
                'forecast_days' => 4,
            ]);

            if (! $respuesta->successful()) {
                Log::warning('Clima: Open-Meteo respondió HTTP ' . $respuesta->status());
                return null;
            }

            return $this->formatear($respuesta->json());
        } catch (Throwable $e) {
            Log::warning('Clima: ' . $e->getMessage());
            return null;
        }
    }

    private function formatear(array $json): ?array
    {
        $actual = $json['current'] ?? null;
        $diario = $json['daily'] ?? null;

        if (! $actual || ! $diario) {
            return null;
        }

        $esDia = (bool) ($actual['is_day'] ?? 1);
        [$descripcion, $icono] = $this->interpretar((int) $actual['weather_code'], $esDia);

        // Pronóstico: hoy + 3 días siguientes
        $pronostico = [];
        foreach ($diario['time'] as $i => $fecha) {
            [$desc, $ico] = $this->interpretar((int) $diario['weather_code'][$i], true);
            $dia = Carbon::parse($fecha);

            $pronostico[] = [
                'dia'         => $i === 0 ? 'Hoy' : ucfirst($dia->translatedFormat('D')),
                'fecha'       => $dia->format('d/m'),
                'maxima'      => (int) round($diario['temperature_2m_max'][$i]),
                'minima'      => (int) round($diario['temperature_2m_min'][$i]),
                'descripcion' => $desc,
                'icono'       => $ico,
            ];
        }

        return [
            'ciudad'      => Configuracion::valor('clima_ciudad', Configuracion::valor('ciudad', '')),
            'temperatura' => (int) round($actual['temperature_2m']),
            'sensacion'   => (int) round($actual['apparent_temperature'] ?? $actual['temperature_2m']),
            'humedad'     => (int) ($actual['relative_humidity_2m'] ?? 0),
            'viento'      => (int) round($actual['wind_speed_10m'] ?? 0),
            'descripcion' => $descripcion,
            'icono'       => $icono,
            'pronostico'  => $pronostico,
            'consultado'  => now()->format('H:i'),
        ];
    }

    private function interpretar(int $codigo, bool $esDia): array
    {
        [$descripcion, $iconoDia, $iconoNoche] = self::CODIGOS[$codigo] ?? ['Sin datos', 'question-circle', 'question-circle'];

        return [$descripcion, $esDia ? $iconoDia : $iconoNoche];
    }
}
