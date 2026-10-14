{{-- Nota en formato horizontal: miniatura a la izquierda y título a la derecha --}}
<article class="nota nota-chica d-flex gap-3">
    <a href="{{ route('nota', $nota) }}" class="nota-imagen flex-shrink-0">
        <img src="{{ $nota->url_imagen }}" alt="{{ $nota->titulo }}" class="img-nota" loading="lazy">
    </a>
    <div>
        @include('publico.partials.volanta', ['nota' => $nota])
        <h3 class="titulo-nota nota-chica-titulo mb-1">
            <a href="{{ route('nota', $nota) }}">{{ $nota->titulo }}</a>
        </h3>
        <small class="text-secondary">{{ $nota->publicada_at->translatedFormat('j \d\e F') }}</small>
    </div>
</article>
