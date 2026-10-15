@extends('layouts.admin')

@section('titulo', 'Publicidades')

@section('contenido')

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <p class="text-secondary mb-0">Los banners vigentes se muestran solos en el sitio según su posición.</p>
        <a href="{{ route('admin.publicidades.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva publicidad
        </a>
    </div>

    {{-- Medidas recomendadas --}}
    <div class="alert alert-light border small">
        <strong><i class="bi bi-rulers me-1"></i> Medidas recomendadas:</strong>
        Header 728 × 90 · Columna lateral 300 × 250 (o 300 × 600) · Entre secciones 970 × 90 · Dentro de la nota 728 × 90 · Pie 970 × 90.
        Para celular conviene una versión de 320 × 100.
    </div>

    @foreach (\App\Models\Publicidad::POSICIONES as $clave => $nombrePosicion)
        @php $lista = $publicidades->get($clave, collect()); @endphp

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $nombrePosicion }}</span>
                <a href="{{ route('admin.publicidades.create', ['posicion' => $clave]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus"></i> Agregar
                </a>
            </div>

            @if ($lista->isEmpty())
                <div class="card-body text-secondary small">Sin publicidades en esta posición.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                            @foreach ($lista as $pub)
                                @php
                                    $hoy = now()->startOfDay();
                                    $vigente = $pub->activa
                                        && (! $pub->desde || $pub->desde->lte($hoy))
                                        && (! $pub->hasta || $pub->hasta->gte($hoy));
                                @endphp
                                <tr class="{{ $vigente ? '' : 'opacity-50' }}">
                                    <td style="width: 180px">
                                        <img src="{{ $pub->url_imagen }}" alt="" class="img-fluid rounded border" style="max-height: 70px">
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $pub->nombre }}</div>
                                        <small class="text-secondary">
                                            @if ($pub->desde || $pub->hasta)
                                                {{ $pub->desde?->format('d/m/Y') ?? 'Siempre' }} → {{ $pub->hasta?->format('d/m/Y') ?? 'sin fin' }}
                                            @else
                                                Sin fecha de fin
                                            @endif
                                            · Orden {{ $pub->orden }}
                                        </small>
                                    </td>
                                    <td class="text-nowrap">
                                        @if (! $pub->activa)
                                            <span class="badge text-bg-secondary">Pausada</span>
                                        @elseif ($vigente)
                                            <span class="badge text-bg-success">Al aire</span>
                                        @elseif ($pub->hasta && $pub->hasta->lt($hoy))
                                            <span class="badge text-bg-dark">Vencida</span>
                                        @else
                                            <span class="badge text-bg-info">Programada</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap small d-none d-md-table-cell">
                                        <i class="bi bi-cursor"></i> {{ number_format($pub->clics, 0, ',', '.') }} clics
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.publicidades.edit', $pub) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('admin.publicidades.destroy', $pub) }}" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar la publicidad {{ $pub->nombre }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach

@endsection
