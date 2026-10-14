@extends('layouts.admin')

@section('titulo', 'Inicio')

@section('contenido')

    <p class="text-secondary mb-4">
        Hola, <strong>{{ auth()->user()->name }}</strong>. Este es el resumen de hoy, {{ now()->translatedFormat('l j \d\e F') }}.
    </p>

    {{-- Tarjetas de resumen --}}
    <div class="row g-3 mb-4">
        @php
            $tarjetas = [
                ['Publicadas hoy', $resumen['publicadas_hoy'], 'check2-circle',  'success'],
                ['Borradores',     $resumen['borradores'],     'pencil-square',  'warning'],
                ['Programadas',    $resumen['programadas'],    'clock-history',  'info'],
                ['Visitas hoy',    number_format($resumen['visitas_hoy'], 0, ',', '.'), 'eye', 'primary'],
            ];
        @endphp

        @foreach ($tarjetas as [$texto, $valor, $icono, $color])
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="admin-icono-tarjeta bg-{{ $color }}-subtle text-{{ $color }}-emphasis">
                            <i class="bi bi-{{ $icono }}"></i>
                        </span>
                        <div>
                            <div class="fs-4 fw-bold lh-1">{{ $valor }}</div>
                            <small class="text-secondary">{{ $texto }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- Últimas notas modificadas --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-clock me-1"></i> Últimas notas modificadas
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Título</th>
                                <th class="d-none d-md-table-cell">Sección</th>
                                <th>Estado</th>
                                <th class="d-none d-sm-table-cell text-end">Visitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ultimas as $nota)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-truncate admin-celda-titulo">{{ $nota->titulo }}</div>
                                        <small class="text-secondary">{{ $nota->autor?->nombre_firma ?? 'Sin autor' }} · {{ $nota->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge" style="background-color: {{ $nota->categoria->color }}">{{ $nota->categoria->nombre }}</span>
                                    </td>
                                    <td>
                                        @if ($nota->estado === \App\Models\Noticia::ESTADO_BORRADOR)
                                            <span class="badge text-bg-warning">Borrador</span>
                                        @elseif ($nota->esta_programada)
                                            <span class="badge text-bg-info">Programada</span>
                                        @else
                                            <span class="badge text-bg-success">Publicada</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-sm-table-cell text-end">{{ number_format($nota->visitas, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4">Todavía no hay notas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Más leídas --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-graph-up-arrow me-1"></i> Más leídas (48 hs)
                </div>
                <ol class="list-group list-group-flush list-group-numbered">
                    @forelse ($masLeidas as $nota)
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                            <div class="ms-1 me-auto small">
                                <div class="fw-semibold">{{ $nota->titulo }}</div>
                                <span class="text-secondary">{{ $nota->categoria->nombre }}</span>
                            </div>
                            <span class="badge text-bg-primary rounded-pill">{{ number_format($nota->visitas_periodo, 0, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-secondary">Sin datos.</li>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>

@endsection
