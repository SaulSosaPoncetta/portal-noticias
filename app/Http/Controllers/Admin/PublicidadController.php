<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicidadRequest;
use App\Models\Publicidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicidadController extends Controller
{
    public function index(): View
    {
        $publicidades = Publicidad::orderBy('posicion')->orderBy('orden')->get()->groupBy('posicion');

        return view('admin.publicidades.index', compact('publicidades'));
    }

    public function create(): View
    {
        $publicidad = new Publicidad(['activa' => true, 'orden' => 0, 'posicion' => request('posicion', 'sidebar')]);

        return view('admin.publicidades.form', compact('publicidad'));
    }

    public function store(PublicidadRequest $request): RedirectResponse
    {
        $publicidad = new Publicidad($request->safe()->except(['imagen', 'imagen_mobile', 'quitar_imagen_mobile']));
        $publicidad->imagen = $this->guardar($request->file('imagen'));

        if ($request->hasFile('imagen_mobile')) {
            $publicidad->imagen_mobile = $this->guardar($request->file('imagen_mobile'));
        }

        $publicidad->save();

        return redirect()->route('admin.publicidades.index')->with('ok', 'Publicidad creada.');
    }

    public function edit(Publicidad $publicidad): View
    {
        return view('admin.publicidades.form', compact('publicidad'));
    }

    public function update(PublicidadRequest $request, Publicidad $publicidad): RedirectResponse
    {
        $publicidad->fill($request->safe()->except(['imagen', 'imagen_mobile', 'quitar_imagen_mobile']));

        if ($request->hasFile('imagen')) {
            $this->borrar($publicidad->imagen);
            $publicidad->imagen = $this->guardar($request->file('imagen'));
        }

        if ($request->hasFile('imagen_mobile')) {
            $this->borrar($publicidad->imagen_mobile);
            $publicidad->imagen_mobile = $this->guardar($request->file('imagen_mobile'));
        } elseif ($request->boolean('quitar_imagen_mobile')) {
            $this->borrar($publicidad->imagen_mobile);
            $publicidad->imagen_mobile = null;
        }

        $publicidad->save();

        return redirect()->route('admin.publicidades.index')->with('ok', 'Publicidad actualizada.');
    }

    public function destroy(Publicidad $publicidad): RedirectResponse
    {
        $this->borrar($publicidad->imagen);
        $this->borrar($publicidad->imagen_mobile);
        $publicidad->delete();

        return redirect()->route('admin.publicidades.index')->with('ok', 'Publicidad eliminada.');
    }

    /** Ruta pública: cuenta el clic y redirige al link del anunciante. */
    public function clic(Publicidad $publicidad): RedirectResponse
    {
        abort_unless($publicidad->url, 404);

        $publicidad->increment('clics');

        return redirect()->away($publicidad->url);
    }

    // ---------------------------------------------------------------

    private function guardar(UploadedFile $archivo): string
    {
        $nombre = Str::random(20) . '.' . strtolower($archivo->getClientOriginalExtension());

        return $archivo->storeAs('publicidades', $nombre, 'public');
    }

    private function borrar(?string $ruta): void
    {
        if ($ruta && ! Str::startsWith($ruta, ['http://', 'https://'])) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
