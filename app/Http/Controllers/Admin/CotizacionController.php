<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Services\ClimaService;
use App\Services\CotizacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CotizacionController extends Controller
{
    public function index(ClimaService $clima): View
    {
        $cotizaciones = Cotizacion::orderBy('orden')->get();

        return view('admin.cotizaciones.index', [
            'cotizaciones' => $cotizaciones,
            'clima'        => $clima->obtener(),
        ]);
    }

    /** Guarda nombre, orden y visibilidad de todas las monedas juntas. */
    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'cotizaciones'           => ['required', 'array'],
            'cotizaciones.*.nombre'  => ['required', 'string', 'max:60'],
            'cotizaciones.*.orden'   => ['required', 'integer', 'min:0', 'max:999'],
            'cotizaciones.*.visible' => ['nullable', 'boolean'],
        ], [
            'cotizaciones.*.nombre.required' => 'Todas las monedas tienen que tener nombre.',
        ]);

        foreach ($datos['cotizaciones'] as $id => $valores) {
            Cotizacion::whereKey($id)->update([
                'nombre'  => $valores['nombre'],
                'orden'   => $valores['orden'],
                'visible' => (bool) ($valores['visible'] ?? false),
            ]);
        }

        return back()->with('ok', 'Cambios guardados.');
    }

    /** Botón "Actualizar ahora": consulta las APIs en el momento. */
    public function actualizar(CotizacionService $cotizaciones, ClimaService $clima): RedirectResponse
    {
        try {
            $cantidad = $cotizaciones->actualizar();
        } catch (Throwable $e) {
            return back()->with('error', 'No se pudieron actualizar las cotizaciones: ' . $e->getMessage());
        }

        $clima->refrescar();

        return back()->with('ok', "Se actualizaron {$cantidad} cotizaciones y el clima.");
    }
}
