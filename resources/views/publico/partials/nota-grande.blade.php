{{-- Nota principal de portada: imagen grande, volanta, título y bajada --}}
<article class="nota nota-grande">
    <a href="{{ route('nota', $nota) }}" class="nota-imagen d-block mb-3">
        <img src="{{ $nota->url_imagen }}" alt="{{ $nota->titulo }}" class="img-nota" fetchpriority="high">
    </a>
    @include('publico.partials.volanta', ['nota' => $nota])
    <h1 class="titulo-nota nota-grande-titulo mt-1">
        <a href="{{ route('nota', $nota) }}">{{ $nota->titulo }}</a>
    </h1>
    @if ($nota->bajada)
        <p class="nota-bajada mb-0">{{ $nota->bajada }}</p>
    @endif
</article>
