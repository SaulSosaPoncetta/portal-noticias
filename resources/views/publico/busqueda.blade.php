@extends('layouts.publico')

@section('titulo', ($texto ? "Buscar: {$texto}" : 'Buscar') . ' | ' . ($config['sitio_nombre'] ?? config('app.name')))

@push('meta')
    <meta name="robots" content="noindex, follow">
@endpush

@section('contenido')
<div class="container">
    <div class="row g-4">

        <div class="col-lg-8">
            <header class="bloque-seccion-cabecera mb-4">
                <h1 class="bloque-seccion-titulo listado-titulo mb-3">Buscar</h1>
                <form action="{{ route('buscar') }}" method="GET" role="search">
                    <div class="input-group input-group-lg">
                        <input type="search" name="q" value="{{ $texto }}" class="form-control" placeholder="¿Qué estás buscando?"
                               minlength="3" maxlength="100" required autofocus>
                        <button class="btn btn-primary"><i class="bi bi-search"></i><span class="d-none d-sm-inline ms-1">Buscar</span></button>
                    </div>
                </form>
            </header>

            @if ($texto !== '' && mb_strlen($texto) < 3)
                <p class="text-secondary">Escribí al menos 3 letras.</p>
            @elseif ($noticias)
                <p class="text-secondary mb-4">
                    @if ($noticias->total() === 0)
                        No encontramos notas con <strong>"{{ $texto }}"</strong>. Probá con otras palabras.
                    @else
                        {{ $noticias->total() }} {{ $noticias->total() === 1 ? 'resultado' : 'resultados' }} para <strong>"{{ $texto }}"</strong>
                    @endif
                </p>

                <div class="d-flex flex-column gap-4">
                    @foreach ($noticias as $nota)
                        @include('publico.partials.nota-chica', ['nota' => $nota])
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
