<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        $categorias = Categoria::withCount('noticias')->orderBy('orden')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        $categoria = new Categoria([
            'color'      => '#d62828',
            'en_menu'    => true,
            'en_portada' => true,
            'activa'     => true,
        ]);

        return view('admin.categorias.form', compact('categoria'));
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['orden'] = (int) Categoria::max('orden') + 1;

        Categoria::create($datos);

        return redirect()->route('admin.categorias.index')->with('ok', 'Sección creada.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('admin.categorias.form', compact('categoria'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('admin.categorias.index')->with('ok', 'Sección actualizada.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->noticias()->withTrashed()->exists()) {
            return back()->with('error', "No se puede eliminar \"{$categoria->nombre}\" porque tiene noticias. Podés desactivarla.");
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')->with('ok', 'Sección eliminada.');
    }

    /** Sube o baja una posición en el orden del menú/portada. */
    public function mover(Categoria $categoria, string $direccion): RedirectResponse
    {
        DB::transaction(function () use ($categoria, $direccion) {
            // Normaliza el orden (1, 2, 3...) por si quedó con huecos
            $lista = Categoria::orderBy('orden')->orderBy('id')->get()->values();
            $lista->each(fn ($c, $i) => $c->orden !== $i + 1 && $c->update(['orden' => $i + 1]));

            $posicion = $lista->search(fn ($c) => $c->id === $categoria->id);
            $vecino = $lista->get($direccion === 'arriba' ? $posicion - 1 : $posicion + 1);

            if ($vecino) {
                $ordenActual = $lista[$posicion]->orden;
                $lista[$posicion]->update(['orden' => $vecino->orden]);
                $vecino->update(['orden' => $ordenActual]);
            }
        });

        return back();
    }
}
