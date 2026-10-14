@extends('layouts.publico')

@push('banner-header')
    @include('publico.partials.publicidad', ['banners' => $publicidades['header'] ?? collect(), 'clase' => 'sitio-publicidad-header'])
@endpush

@section('contenido')
<div class="container">

    @if (! $principal)
        <div class="text-center text-secondary py-5">
            <i class="bi bi-newspaper display-4 d-block mb-3"></i>
            Todavía no hay noticias publicadas.
        </div>
    @else

        <div class="row g-4">

            {{-- ==================== Columna principal ==================== --}}
            <div class="col-lg-8">

                {{-- Nota principal + 2 secundarias --}}
                <div class="row g-4 pb-4 mb-4 border-bottom">
                    <div class="col-md-8">
                        @include('publico.partials.nota-grande', ['nota' => $principal])
                    </div>
                    <div class="col-md-4 d-flex flex-column gap-4">
                        @foreach ($secundarias as $nota)
                            @include('publico.partials.nota-media', ['nota' => $nota])
                        @endforeach
                    </div>
                </div>

                {{-- Grilla de notas --}}
                @if ($grilla->isNotEmpty())
                    <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3">
                        @foreach ($grilla as $nota)
                            <div class="col">
                                @include('publico.partials.nota-media', ['nota' => $nota])
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ==================== Columna lateral ==================== --}}
            <aside class="col-lg-4">
                <div class="sitio-sidebar">
                    @include('publico.partials.sidebar')
                </div>
            </aside>
        </div>

        {{-- ==================== Bloques por sección ==================== --}}
        @foreach ($bloques as $bloque)
            @php
                $categoria = $bloque['categoria'];
                $notas = $bloque['notas'];
            @endphp

            {{-- Publicidad cada 2 bloques --}}
            @if ($loop->index > 0 && $loop->index % 2 === 0)
                @include('publico.partials.publicidad', ['banners' => $publicidades['entre_bloques'] ?? collect(), 'clase' => 'my-4'])
            @endif

            <section class="bloque-seccion mt-5" style="--color-seccion: {{ $categoria->color }}">
                <header class="bloque-seccion-cabecera d-flex justify-content-between align-items-end mb-3">
                    <h2 class="bloque-seccion-titulo mb-0">
                        <a href="{{ route('seccion', $categoria) }}">{{ $categoria->nombre }}</a>
                    </h2>
                    <a href="{{ route('seccion', $categoria) }}" class="bloque-seccion-mas">Ver más <i class="bi bi-chevron-right"></i></a>
                </header>

                <div class="row g-4">
                    {{-- Primera nota grande --}}
                    <div class="col-lg-6">
                        @include('publico.partials.nota-media', ['nota' => $notas->first(), 'conBajada' => true])
                    </div>

                    {{-- Resto en lista --}}
                    <div class="col-lg-6 d-flex flex-column gap-3">
                        @foreach ($notas->skip(1) as $nota)
                            @include('publico.partials.nota-chica', ['nota' => $nota])
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

    @endif
</div>
@endsection
