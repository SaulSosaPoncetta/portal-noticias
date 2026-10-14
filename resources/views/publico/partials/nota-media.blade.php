{{-- Tarjeta con imagen arriba y título (grilla y notas secundarias) --}}
<article class="nota nota-media h-100">
    <a href="{{ route('nota', $nota) }}" class="nota-imagen d-block mb-2">
        <img src="{{ $nota->url_imagen }}" alt="{{ $nota->titulo }}" class="img-nota" loading="lazy">
    </a>
    @include('publico.partials.volanta', ['nota' => $nota])
    <h2 class="titulo-nota nota-media-titulo mt-1 mb-0">
        <a href="{{ route('nota', $nota) }}">{{ $nota->titulo }}</a>
    </h2>
    @if (! empty($conBajada) && $nota->bajada)
        <p class="nota-bajada small mt-2 mb-0">{{ \Illuminate\Support\Str::limit($nota->bajada, 160) }}</p>
    @endif
</article>
