{{--
    Muestra los banners vigentes de una posición.
    Uso: @include('publico.partials.publicidad', ['banners' => $publicidades['sidebar'] ?? collect()])
    La carga de publicidades desde el panel se agrega en el Módulo 10.
--}}
@if (! empty($banners) && $banners->isNotEmpty())
    <div class="sitio-publicidad {{ $clase ?? '' }}">
        @foreach ($banners as $banner)
            <a href="{{ $banner->url ?: '#' }}" @if ($banner->url) target="_blank" rel="noopener sponsored" @endif class="d-block mb-3">
                <picture>
                    @if ($banner->url_imagen_mobile)
                        <source media="(max-width: 767px)" srcset="{{ $banner->url_imagen_mobile }}">
                    @endif
                    <img src="{{ $banner->url_imagen }}" alt="{{ $banner->nombre }}" class="img-fluid w-100" loading="lazy">
                </picture>
            </a>
        @endforeach
        <small class="sitio-publicidad-etiqueta">Publicidad</small>
    </div>
@endif
