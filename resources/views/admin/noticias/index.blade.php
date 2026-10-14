@extends('layouts.admin')

@section('titulo', 'Noticias')

@section('contenido')

    @php
        $estados = [
            ''           => 'Todas',
            'publicada'  => 'Publicadas',
            'programada' => 'Programadas',
            'borrador'   => 'Borradores',
            'papelera'   => 'Papelera',
        ];
        $estadoActual = request('estado', '');
        $enPapelera = $estadoActual === 'papelera';
    @endphp

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        {{-- Pestañas de estado --}}
        <ul class="nav nav-pills small">
            @foreach ($estados as $valor => $texto)
                <li class="nav-item">
                    <a href="{{ route('admin.noticias.index', array_filter(['estado' => $valor] + request()->except('estado', 'page'))) }}"
                       class="nav-link py-1 {{ $estadoActual === $valor ? 'active' : '' }}">{{ $texto }}</a>
                </li>
            @endforeach
        </ul>

        @can('create', \App\Models\Noticia::class)
            <a href="{{ route('admin.noticias.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Nueva nota
            </a>
        @endcan
    </div>

    {{-- Filtros --}}
    <form method="GET" class="card border-0 shadow-sm mb-3">
        <div class="card-body row g-2 align-items-end">
            <input type="hidden" name="estado" value="{{ $estadoActual }}">

            <div class="col-md-5">
                <label class="form-label small mb-1">Buscar</label>
                <input type="search" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Título o volanta">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Sección</label>
                <select name="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected(request('categoria_id') == $cat->id)>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            @if ($autores->isNotEmpty())
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Autor</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($autores as $autor)
                            <option value="{{ $autor->id }}" @selected(request('user_id') == $autor->id)>{{ $autor->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1"><i class="bi bi-funnel"></i> Filtrar</button>
                @if (request()->hasAny(['buscar', 'categoria_id', 'user_id']))
                    <a href="{{ route('admin.noticias.index', array_filter(['estado' => $estadoActual])) }}" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </div>
    </form>

    {{-- Listado --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px" class="d-none d-md-table-cell"></th>
                        <th>Nota</th>
                        <th class="d-none d-lg-table-cell">Sección</th>
                        <th>Estado</th>
                        <th class="d-none d-sm-table-cell text-end">Visitas</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($noticias as $nota)
                        <tr>
                            <td class="d-none d-md-table-cell">
                                <img src="{{ $nota->url_imagen }}" alt="" class="rounded" style="width: 80px; aspect-ratio: 16/9; object-fit: cover" loading="lazy">
                            </td>
                            <td>
                                @if ($nota->volanta)
                                    <small class="volanta" style="color: {{ $nota->categoria->color }}">{{ $nota->volanta }}</small>
                                @endif
                                <div class="fw-semibold admin-celda-titulo">
                                    @if ($nota->destacada)
                                        <i class="bi bi-star-fill text-warning" title="Destacada"></i>
                                    @endif
                                    {{ $nota->titulo }}
                                </div>
                                <small class="text-secondary">
                                    {{ $nota->autor?->nombre_firma ?? 'Sin autor' }} ·
                                    {{ ($nota->publicada_at ?? $nota->created_at)->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <span class="badge" style="background-color: {{ $nota->categoria->color }}">{{ $nota->categoria->nombre }}</span>
                            </td>
                            <td>
                                @if ($nota->trashed())
                                    <span class="badge text-bg-dark">En papelera</span>
                                @elseif ($nota->estado === \App\Models\Noticia::ESTADO_BORRADOR)
                                    <span class="badge text-bg-warning">Borrador</span>
                                @elseif ($nota->esta_programada)
                                    <span class="badge text-bg-info">Programada</span>
                                @else
                                    <span class="badge text-bg-success">Publicada</span>
                                @endif
                            </td>
                            <td class="d-none d-sm-table-cell text-end">{{ number_format($nota->visitas, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap">
                                @if ($enPapelera)
                                    @can('restore', $nota)
                                        <form method="POST" action="{{ route('admin.noticias.restaurar', $nota->id) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i> Restaurar</button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $nota)
                                        <a href="{{ route('admin.noticias.edit', $nota) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $nota)
                                        <form method="POST" action="{{ route('admin.noticias.destroy', $nota) }}" class="d-inline"
                                              onsubmit="return confirm('¿Enviar esta nota a la papelera?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Enviar a la papelera"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-5">No hay notas con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($noticias->hasPages())
            <div class="card-footer bg-white">
                {{ $noticias->links() }}
            </div>
        @endif
    </div>

@endsection
