@extends('layouts.admin')

@section('titulo', 'Secciones')

@section('contenido')

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <p class="text-secondary mb-0">El orden de esta lista es el orden del menú y de los bloques de la portada.</p>
        <a href="{{ route('admin.categorias.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva sección
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px">Orden</th>
                        <th>Sección</th>
                        <th class="d-none d-md-table-cell">URL</th>
                        <th class="text-center">Notas</th>
                        <th class="d-none d-sm-table-cell">Se muestra en</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $categoria)
                        <tr class="{{ $categoria->activa ? '' : 'opacity-50' }}">
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="{{ route('admin.categorias.mover', [$categoria, 'arriba']) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-outline-secondary" title="Subir" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.categorias.mover', [$categoria, 'abajo']) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-outline-secondary" title="Bajar" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <span class="d-inline-block rounded-circle me-2 align-middle" style="width:14px;height:14px;background:{{ $categoria->color }}"></span>
                                <span class="fw-semibold">{{ $categoria->nombre }}</span>
                                @unless ($categoria->activa)
                                    <span class="badge text-bg-secondary ms-1">Inactiva</span>
                                @endunless
                            </td>
                            <td class="d-none d-md-table-cell"><code>/seccion/{{ $categoria->slug }}</code></td>
                            <td class="text-center">{{ $categoria->noticias_count }}</td>
                            <td class="d-none d-sm-table-cell">
                                @if ($categoria->en_menu)
                                    <span class="badge text-bg-light border">Menú</span>
                                @endif
                                @if ($categoria->en_portada)
                                    <span class="badge text-bg-light border">Portada</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.categorias.edit', $categoria) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.categorias.destroy', $categoria) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar la sección {{ $categoria->nombre }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">No hay secciones cargadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
