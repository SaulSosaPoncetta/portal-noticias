@extends('layouts.publico')

@php
    $sitio = $config['sitio_nombre'] ?? config('app.name');
    $tituloSeo = $noticia->meta_titulo ?: $noticia->titulo;
    $descripcionSeo = $noticia->meta_descripcion ?: \Illuminate\Support\Str::limit(strip_tags($noticia->bajada ?: $noticia->cuerpo), 160);
    $autor = $noticia->autor?->nombre_firma ?? 'Redacción';

    // Datos estructurados para Google News
    $jsonLd = [
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'headline'         => \Illuminate\Support\Str::limit($noticia->titulo, 110, ''),
        'description'      => $descripcionSeo,
        'image'            => [$noticia->url_imagen],
        'datePublished'    => $noticia->publicada_at?->toIso8601String(),
        'dateModified'     => $noticia->updated_at->toIso8601String(),
        'articleSection'   => $noticia->categoria->nombre,
        'author'           => ['@type' => 'Person', 'name' => $autor],
        'publisher'        => ['@type' => 'Organization', 'name' => $sitio],
        'mainEntityOfPage' => route('nota', $noticia),
    ];
@endphp

@section('titulo', $tituloSeo . ' | ' . $sitio)
@section('descripcion', $descripcionSeo)

@push('meta')
    @if ($vistaPrevia)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $tituloSeo }}">
    <meta property="og:description" content="{{ $descripcionSeo }}">
    <meta property="og:url" content="{{ route('nota', $noticia) }}">
    <meta property="og:image" content="{{ $noticia->url_imagen }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="675">
    <meta property="article:published_time" content="{{ $noticia->publicada_at?->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $noticia->updated_at->toIso8601String() }}">
    <meta property="article:section" content="{{ $noticia->categoria->nombre }}">
    @foreach ($noticia->tags as $tag)
        <meta property="article:tag" content="{{ $tag->nombre }}">
    @endforeach
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $tituloSeo }}">
    <meta name="twitter:description" content="{{ $descripcionSeo }}">
    <meta name="twitter:image" content="{{ $noticia->url_imagen }}">

    {{-- Datos estructurados para Google News --}}
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('contenido')
<div class="container">

    @if ($vistaPrevia)
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bi bi-eye-fill"></i>
            <div>
                <strong>Vista previa.</strong>
                Esta nota está {{ $noticia->estado === 'borrador' ? 'en borrador' : 'programada para el ' . $noticia->publicada_at->format('d/m/Y H:i') }}
                y el público todavía no la ve.
                @can('update', $noticia)
                    <a href="{{ route('admin.noticias.edit', $noticia) }}" class="alert-link ms-1">Editar</a>
                @endcan
            </div>
        </div>
    @endif

    <div class="row g-4">

        {{-- ==================== Nota ==================== --}}
        <article class="col-lg-8">

            {{-- Migas de pan --}}
            <nav aria-label="breadcrumb" class="small mb-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('seccion', $noticia->categoria) }}">{{ $noticia->categoria->nombre }}</a></li>
                </ol>
            </nav>

            <header class="mb-4">
                @if ($noticia->volanta)
                    <span class="volanta" style="color: {{ $noticia->categoria->color }}">{{ $noticia->volanta }}</span>
                @endif

                <h1 class="titulo-nota nota-titulo mt-1">{{ $noticia->titulo }}</h1>

                @if ($noticia->bajada)
                    <p class="nota-bajada nota-bajada-grande">{{ $noticia->bajada }}</p>
                @endif

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 py-3 border-top border-bottom mt-3">
                    <div class="small text-secondary">
                        <span class="fw-semibold text-body">{{ $autor }}</span>
                        <span class="mx-1">·</span>
                        @if ($noticia->publicada_at)
                            <time datetime="{{ $noticia->publicada_at->toIso8601String() }}">
                                {{ $noticia->publicada_at->translatedFormat('j \d\e F \d\e Y · H:i') }}
                            </time>
                        @endif
                        <span class="mx-1">·</span>
                        <i class="bi bi-clock"></i> {{ $minutos }} min de lectura
                    </div>
                    @include('publico.partials.compartir')
                </div>
            </header>

            {{-- Imagen o video principal --}}
            @if ($videoId)
                <div class="ratio ratio-16x9 mb-4 rounded overflow-hidden">
                    <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}" title="{{ $noticia->titulo }}"
                            allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                </div>
            @elseif ($noticia->imagen)
                <figure class="mb-4">
                    <img src="{{ $noticia->url_imagen }}" alt="{{ $noticia->epigrafe ?: $noticia->titulo }}" class="img-nota" fetchpriority="high">
                    @if ($noticia->epigrafe)
                        <figcaption class="nota-epigrafe">{{ $noticia->epigrafe }}</figcaption>
                    @endif
                </figure>
            @endif

            {{-- Cuerpo --}}
            <div class="nota-cuerpo">
                {!! $noticia->cuerpo !!}
            </div>

            @include('publico.partials.publicidad', ['banners' => $bannersNota, 'clase' => 'my-4'])

            {{-- Etiquetas --}}
            @if ($noticia->tags->isNotEmpty())
                <div class="nota-tags d-flex flex-wrap gap-2 my-4">
                    @foreach ($noticia->tags as $tag)
                        <a href="{{ route('tema', $tag) }}" class="badge rounded-pill text-bg-light border"># {{ $tag->nombre }}</a>
                    @endforeach
                </div>
            @endif

            <div class="py-3 border-top border-bottom mb-5">
                @include('publico.partials.compartir')
            </div>

            {{-- Relacionadas --}}
            @if ($relacionadas->isNotEmpty())
                <section class="bloque-seccion" style="--color-seccion: {{ $noticia->categoria->color }}">
                    <header class="bloque-seccion-cabecera mb-3">
                        <h2 class="bloque-seccion-titulo mb-0">Te puede interesar</h2>
                    </header>
                    <div class="row g-4 row-cols-1 row-cols-sm-2">
                        @foreach ($relacionadas as $nota)
                            <div class="col">
                                @include('publico.partials.nota-media', ['nota' => $nota])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>

        {{-- ==================== Columna lateral ==================== --}}
        <aside class="col-lg-4">
            <div class="sitio-sidebar">
                @include('publico.partials.sidebar')
            </div>
        </aside>
    </div>
</div>
@endsection
