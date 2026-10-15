{{-- Listado de notas por sección o por etiqueta --}}
@extends('layouts.publico')

@php
    $sitio = $config['sitio_nombre'] ?? config('app.name');
    $pagina = $noticias->currentPage();
@endphp

@section('titulo', $titulo . ($pagina > 1 ? " - Página {$pagina}" : '') . ' | ' . $sitio)
@section('descripcion', $descripcion ?: "Últimas noticias de {$titulo}.")

@push('meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $titulo }} | {{ $sitio }}">
    @if ($noticias->previousPageUrl())
        <link rel="prev" href="{{ $noticias->previousPageUrl() }}">
    @endif
    @if ($noticias->nextPageUrl())
        <link rel="next" href="{{ $noticias->nextPageUrl() }}">
    @endif
@endpush

@section('contenido')
<div class="container">
    <div class="row g-4">

        <div class="col-lg-8">
            <header class="bloque-seccion-cabecera mb-4" style="--color-seccion: {{ $color ?? '#0b3d91' }}">
                <small class="text-uppercase text-secondary fw-semibold">{{ $etiqueta }}</small>
                <h1 class="bloque-seccion-titulo listado-titulo mb-0" style="color: {{ $color ?? '#0b3d91' }}">
                    {{ $etiqueta === 'Tema' ? '#' : '' }}{{ $titulo }}
                </h1>
            </header>

            @if ($noticias->isEmpty())
                <p class="text-secondary py-5 text-center">Todavía no hay notas publicadas en esta sección.</p>
            @else
                {{-- En la primera página, la nota más reciente va en grande --}}
                @if ($noticias->onFirstPage())
                    <div class="pb-4 mb-4 border-bottom">
                        @include('publico.partials.nota-grande', ['nota' => $noticias->first()])
                    </div>
                    @php $resto = $noticias->getCollection()->skip(1); @endphp
                @else
                    @php $resto = $noticias->getCollection(); @endphp
                @endif

                <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3">
                    @foreach ($resto as $nota)
                        <div class="col">
                            @include('publico.partials.nota-media', ['nota' => $nota])
                        </div>
                    @endforeach
                </div>

                @if ($noticias->hasPages())
                    <nav class="mt-5 d-flex justify-content-center">
                        {{ $noticias->onEachSide(1)->links() }}
                    </nav>
                @endif
            @endif
        </div>

        <aside class="col-lg-4">
            <div class="sitio-sidebar">
                @include('publico.partials.sidebar')
            </div>
        </aside>
    </div>
</div>
@endsection
