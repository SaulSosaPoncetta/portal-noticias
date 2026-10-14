{{-- Columna lateral: últimas noticias, cotizaciones, más leídas y publicidad --}}

@include('publico.partials.publicidad', ['banners' => $publicidades['sidebar'] ?? collect()])

{{-- Últimas noticias --}}
@if ($ultimas->isNotEmpty())
    <section class="widget mb-4">
        <h2 class="widget-titulo"><i class="bi bi-lightning-charge-fill"></i> Últimas noticias</h2>
        <ul class="list-unstyled widget-ultimas mb-0">
            @foreach ($ultimas as $nota)
                <li>
                    <time datetime="{{ $nota->publicada_at->toIso8601String() }}">{{ $nota->publicada_at->format('H:i') }}</time>
                    <a href="{{ route('nota', $nota) }}">{{ $nota->titulo }}</a>
                </li>
            @endforeach
        </ul>
    </section>
@endif

{{-- Clima --}}
@if (! empty($clima))
    <section class="widget mb-4">
        <h2 class="widget-titulo"><i class="bi bi-cloud-sun-fill"></i> Clima en {{ $clima['ciudad'] }}</h2>
        <div class="widget-clima-actual d-flex align-items-center gap-3 mb-3">
            <i class="bi bi-{{ $clima['icono'] }}"></i>
            <div>
                <div class="widget-clima-temp">{{ $clima['temperatura'] }}°</div>
                <div class="small">{{ $clima['descripcion'] }}</div>
            </div>
            <ul class="list-unstyled small text-secondary ms-auto mb-0 text-end">
                <li>Sensación {{ $clima['sensacion'] }}°</li>
                <li><i class="bi bi-droplet"></i> {{ $clima['humedad'] }}%</li>
                <li><i class="bi bi-wind"></i> {{ $clima['viento'] }} km/h</li>
            </ul>
        </div>
        <div class="row g-2 text-center widget-clima-pronostico">
            @foreach ($clima['pronostico'] as $dia)
                <div class="col-3">
                    <div class="fw-semibold small">{{ $dia['dia'] }}</div>
                    <i class="bi bi-{{ $dia['icono'] }}" title="{{ $dia['descripcion'] }}"></i>
                    <div class="small"><strong>{{ $dia['maxima'] }}°</strong> <span class="text-secondary">{{ $dia['minima'] }}°</span></div>
                </div>
            @endforeach
        </div>
    </section>
@endif

{{-- Cotizaciones --}}
@if ($cotizaciones->isNotEmpty())
    <section class="widget mb-4">
        <h2 class="widget-titulo"><i class="bi bi-currency-dollar"></i> Cotizaciones</h2>
        <table class="table table-sm widget-cotizaciones mb-1">
            <thead>
                <tr>
                    <th></th>
                    <th class="text-end">Compra</th>
                    <th class="text-end">Venta</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cotizaciones as $cotizacion)
                    <tr>
                        <td>{{ $cotizacion->nombre }}</td>
                        <td class="text-end">{{ \App\Models\Cotizacion::formatear($cotizacion->compra) }}</td>
                        <td class="text-end fw-semibold">{{ \App\Models\Cotizacion::formatear($cotizacion->venta) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($actualizada = $cotizaciones->sortByDesc('actualizada_at')->first()?->actualizada_at)
            <small class="text-secondary">Actualizado: {{ $actualizada->format('d/m H:i') }}</small>
        @endif
    </section>
@endif

{{-- Más leídas --}}
@if ($masLeidas->isNotEmpty())
    <section class="widget mb-4">
        <h2 class="widget-titulo"><i class="bi bi-fire"></i> Más leídas</h2>
        <ol class="list-unstyled widget-ranking mb-0">
            @foreach ($masLeidas as $nota)
                <li>
                    <span class="widget-ranking-numero">{{ $loop->iteration }}</span>
                    <div>
                        @include('publico.partials.volanta', ['nota' => $nota])
                        <a href="{{ route('nota', $nota) }}" class="titulo-nota d-block">{{ $nota->titulo }}</a>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
@endif
