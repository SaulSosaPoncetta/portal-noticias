@extends('layouts.admin')

@section('titulo', 'Cotizaciones y clima')

@section('contenido')

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <p class="text-secondary mb-0">
            Se actualizan solas cada 30 minutos desde <strong>dolarapi.com</strong> y <strong>Open-Meteo</strong>.
        </p>
        <form method="POST" action="{{ route('admin.cotizaciones.actualizar') }}">
            @csrf
            <button class="btn btn-outline-primary"><i class="bi bi-arrow-repeat me-1"></i> Actualizar ahora</button>
        </form>
    </div>

    <div class="row g-4">

        {{-- Cotizaciones --}}
        <div class="col-xl-8">
            <form method="POST" action="{{ route('admin.cotizaciones.update') }}" class="card border-0 shadow-sm">
                @csrf @method('PUT')

                <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-dollar me-1"></i> Monedas</div>

                @error('cotizaciones.*')
                    <div class="alert alert-danger m-3 mb-0">{{ $message }}</div>
                @enderror

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px">Orden</th>
                                <th>Nombre que se muestra</th>
                                <th class="text-end">Compra</th>
                                <th class="text-end">Venta</th>
                                <th class="d-none d-md-table-cell">Actualizado</th>
                                <th class="text-center">Visible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cotizaciones as $c)
                                <tr class="{{ $c->visible ? '' : 'text-secondary' }}">
                                    <td>
                                        <input type="number" name="cotizaciones[{{ $c->id }}][orden]" value="{{ old("cotizaciones.{$c->id}.orden", $c->orden) }}"
                                               min="0" max="999" class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="text" name="cotizaciones[{{ $c->id }}][nombre]" value="{{ old("cotizaciones.{$c->id}.nombre", $c->nombre) }}"
                                               maxlength="60" class="form-control form-control-sm">
                                        <small class="text-secondary"><code>{{ $c->codigo }}</code></small>
                                    </td>
                                    <td class="text-end text-nowrap">{{ \App\Models\Cotizacion::formatear($c->compra) }}</td>
                                    <td class="text-end text-nowrap fw-semibold">{{ \App\Models\Cotizacion::formatear($c->venta) }}</td>
                                    <td class="d-none d-md-table-cell small">{{ $c->actualizada_at?->format('d/m H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input type="hidden" name="cotizaciones[{{ $c->id }}][visible]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="cotizaciones[{{ $c->id }}][visible]" value="1" @checked($c->visible)>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-secondary py-4">Sin cotizaciones. Tocá "Actualizar ahora".</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar cambios</button>
                </div>
            </form>
        </div>

        {{-- Clima --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-cloud-sun me-1"></i> Clima</div>
                <div class="card-body">
                    @if ($clima)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-{{ $clima['icono'] }} display-5 text-warning"></i>
                            <div>
                                <div class="fs-2 fw-bold lh-1">{{ $clima['temperatura'] }}°</div>
                                <div class="text-secondary">{{ $clima['ciudad'] }} · {{ $clima['descripcion'] }}</div>
                            </div>
                        </div>
                        <small class="text-secondary">Última consulta: {{ $clima['consultado'] }}</small>
                    @else
                        <p class="text-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i> No se pudo obtener el clima.</p>
                    @endif
                    <hr>
                    <p class="small text-secondary mb-0">
                        La ciudad y las coordenadas (<code>clima_ciudad</code>, <code>clima_lat</code>, <code>clima_lon</code>)
                        se cambian en Configuración (Módulo 10). Para buscar las coordenadas de otra ciudad podés usar Google Maps:
                        clic derecho sobre el lugar y copiar los números.
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
