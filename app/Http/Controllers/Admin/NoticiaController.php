<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticiaRequest;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NoticiaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Noticia::class);

        $usuario = $request->user();
        $estado = $request->input('estado');

        $noticias = Noticia::query()
            ->with(['categoria', 'autor'])
            // Filtro por estado
            ->when($estado === 'papelera', fn ($q) => $q->onlyTrashed())
            ->when($estado === 'borrador', fn ($q) => $q->where('estado', Noticia::ESTADO_BORRADOR))
            ->when($estado === 'publicada', fn ($q) => $q->publicadas())
            ->when($estado === 'programada', fn ($q) => $q->where('estado', Noticia::ESTADO_PUBLICADA)->where('publicada_at', '>', now()))
            // El redactor solo ve sus notas
            ->when(! $usuario->can('noticias.editar_todas'), fn ($q) => $q->where('user_id', $usuario->id))
            // Resto de filtros
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->integer('categoria_id')))
            ->when($request->filled('user_id') && $usuario->can('noticias.editar_todas'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $texto = '%' . $request->input('buscar') . '%';
                $q->where(fn ($w) => $w->where('titulo', 'like', $texto)->orWhere('volanta', 'like', $texto));
            })
            ->orderByRaw('COALESCE(publicada_at, created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        $categorias = Categoria::orderBy('orden')->get(['id', 'nombre']);
        $autores = $usuario->can('noticias.editar_todas') ? User::orderBy('name')->get(['id', 'name']) : collect();

        return view('admin.noticias.index', compact('noticias', 'categorias', 'autores'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Noticia::class);

        $noticia = new Noticia([
            'estado'       => Noticia::ESTADO_BORRADOR,
            'publicada_at' => now(),
            'categoria_id' => $request->integer('categoria_id') ?: null,
        ]);

        return view('admin.noticias.form', $this->datosFormulario($noticia));
    }

    public function store(NoticiaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Noticia::class);

        $noticia = new Noticia($this->armarDatos($request));
        $noticia->user_id = $request->user()->id;

        if ($request->hasFile('imagen')) {
            $noticia->imagen = $this->guardarImagen($request->file('imagen'));
        }

        $noticia->save();
        $this->sincronizarTags($noticia, $request->input('tags'));

        return redirect()
            ->route('admin.noticias.edit', $noticia)
            ->with('ok', $this->mensajeGuardado($noticia, 'creada'));
    }

    public function edit(Noticia $noticia): View
    {
        Gate::authorize('update', $noticia);

        $noticia->load('tags');

        return view('admin.noticias.form', $this->datosFormulario($noticia));
    }

    public function update(NoticiaRequest $request, Noticia $noticia): RedirectResponse
    {
        Gate::authorize('update', $noticia);

        $noticia->fill($this->armarDatos($request));

        if ($request->hasFile('imagen')) {
            $this->borrarImagen($noticia->imagen);
            $noticia->imagen = $this->guardarImagen($request->file('imagen'));
        } elseif ($request->boolean('quitar_imagen')) {
            $this->borrarImagen($noticia->imagen);
            $noticia->imagen = null;
        }

        $noticia->save();
        $this->sincronizarTags($noticia, $request->input('tags'));

        return redirect()
            ->route('admin.noticias.edit', $noticia)
            ->with('ok', $this->mensajeGuardado($noticia, 'actualizada'));
    }

    /** Envía la nota a la papelera (borrado lógico). */
    public function destroy(Noticia $noticia): RedirectResponse
    {
        Gate::authorize('delete', $noticia);

        $noticia->delete();

        return redirect()->route('admin.noticias.index')->with('ok', 'La nota se envió a la papelera.');
    }

    public function restaurar(int $id): RedirectResponse
    {
        $noticia = Noticia::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $noticia);

        $noticia->restore();

        return redirect()->route('admin.noticias.index', ['estado' => 'papelera'])->with('ok', 'Nota restaurada.');
    }

    /** Subida de imágenes desde el editor de texto (Quill). Devuelve la URL. */
    public function subirImagen(Request $request): JsonResponse
    {
        Gate::authorize('create', Noticia::class);

        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $ruta = $this->guardarImagen($request->file('imagen'), 'cuerpo');

        return response()->json(['url' => Storage::disk('public')->url($ruta)]);
    }

    // ---------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------

    private function datosFormulario(Noticia $noticia): array
    {
        return [
            'noticia'    => $noticia,
            'categorias' => Categoria::activas()->get(['id', 'nombre']),
            'puedePublicar' => Gate::allows('publish', Noticia::class),
        ];
    }

    private function armarDatos(NoticiaRequest $request): array
    {
        $datos = $request->safe()->except(['imagen', 'quitar_imagen', 'tags']);

        // Sin permiso de publicar, siempre queda en borrador
        if (! Gate::allows('publish', Noticia::class)) {
            $datos['estado'] = Noticia::ESTADO_BORRADOR;
            $datos['destacada'] = false;
            $datos['orden_portada'] = null;
        }

        // Publicada sin fecha = ahora
        if ($datos['estado'] === Noticia::ESTADO_PUBLICADA && empty($datos['publicada_at'])) {
            $datos['publicada_at'] = now();
        }

        $datos['cuerpo'] = $this->limpiarHtml($datos['cuerpo']);

        return $datos;
    }

    /** Quita scripts y atributos de eventos (onclick, onerror...) del HTML del editor. */
    private function limpiarHtml(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        $html = preg_replace('#(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1="#"', $html);

        return trim($html);
    }

    private function guardarImagen(UploadedFile $archivo, string $subcarpeta = 'portadas'): string
    {
        $carpeta = 'noticias/' . $subcarpeta . '/' . now()->format('Y/m');
        $nombre = Str::random(20) . '.' . strtolower($archivo->getClientOriginalExtension());

        return $archivo->storeAs($carpeta, $nombre, 'public');
    }

    private function borrarImagen(?string $ruta): void
    {
        // Las imágenes de ejemplo son URLs externas: no hay archivo que borrar
        if ($ruta && ! Str::startsWith($ruta, ['http://', 'https://'])) {
            Storage::disk('public')->delete($ruta);
        }
    }

    private function sincronizarTags(Noticia $noticia, ?string $texto): void
    {
        $ids = collect(explode(',', (string) $texto))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique(fn ($t) => Str::slug($t))
            ->take(10)
            ->map(fn ($t) => Tag::obtenerOCrear($t)->id);

        $noticia->tags()->sync($ids);
    }

    private function mensajeGuardado(Noticia $noticia, string $accion): string
    {
        return match (true) {
            $noticia->estado === Noticia::ESTADO_BORRADOR => "Nota {$accion} como borrador.",
            $noticia->esta_programada => "Nota {$accion}. Se publicará el " . $noticia->publicada_at->format('d/m/Y \a \l\a\s H:i') . '.',
            default => "Nota {$accion} y publicada.",
        };
    }
}
