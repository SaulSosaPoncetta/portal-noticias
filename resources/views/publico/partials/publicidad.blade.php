{{--
    Muestra los banners vigentes de una posición.
    Uso: @include('publico.partials.publicidad', ['banners' => $coleccion, 'clase' => 'my-4'])
    Si el banner tiene link, el clic pasa por /publicidad/{id}/clic para contarlo.
--}}
@if (! empty($banners) && $banners->isNotEmpty())
    <div class="sitio-publicidad {{ $clase ?? '' }}">
        @foreach ($banners as $banner)
            @if ($banner->url)
                <a href="{{ route('publicidad.clic', $banner) }}" target="_blank" rel="noopener sponsored" class="d-block mb-3">
            @else
                <div class="mb-3">
            @endif

                <picture>
                    @if ($banner->url_imagen_mobile)
                        <source media="(max-width: 767px)" srcset="{{ $banner->url_imagen_mobile }}">
                    @endif
                    <img src="{{ $banner->url_imagen }}" alt="{{ $banner->nombre }}" class="img-fluid w-100" loading="lazy">
                </picture>

            @if ($banner->url)
                </a>
            @else
                </div>
            @endif
        @endforeach
        <small class="sitio-publicidad-etiqueta">Publicidad</small>
    </div>
@endif
