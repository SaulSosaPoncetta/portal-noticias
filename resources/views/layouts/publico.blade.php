@php
    $sitioNombre = $config['sitio_nombre'] ?? config('app.name');
    $redes = [
        'facebook'  => ['facebook',  'Facebook'],
        'instagram' => ['instagram', 'Instagram'],
        'youtube'   => ['youtube',   'YouTube'],
        'x_twitter' => ['twitter-x', 'X'],
    ];
    $whatsapp = $config['whatsapp'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', $sitioNombre . ' | ' . ($config['sitio_eslogan'] ?? ''))</title>
    <meta name="description" content="@yield('descripcion', $config['seo_descripcion'] ?? '')">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph (se completa por nota en el Módulo 9) --}}
    <meta property="og:site_name" content="{{ $sitioNombre }}">
    <meta property="og:locale" content="es_AR">
    @stack('meta')

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="sitio-body">

    {{-- ==================== Barra superior ==================== --}}
    <div class="sitio-topbar">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="text-capitalize">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l j \d\e F \d\e Y') }}
            </span>

            <div class="d-flex align-items-center gap-3">
                {{-- Clima actual --}}
                @if ($clima)
                    <span class="sitio-topbar-clima" title="{{ $clima['descripcion'] }}">
                        <i class="bi bi-{{ $clima['icono'] }}"></i>
                        <strong>{{ $clima['temperatura'] }}°</strong>
                        <span class="d-none d-md-inline">{{ $clima['ciudad'] }}</span>
                    </span>
                @endif

                <span class="d-none d-sm-flex gap-2">
                    @foreach ($redes as $clave => [$icono, $nombre])
                        @if (! empty($config[$clave]))
                            <a href="{{ $config[$clave] }}" target="_blank" rel="noopener" title="{{ $nombre }}"><i class="bi bi-{{ $icono }}"></i></a>
                        @endif
                    @endforeach
                </span>
            </div>
        </div>
    </div>

    {{-- ==================== Cabecera ==================== --}}
    <header class="sitio-header">
        <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3 py-3">
            <a href="{{ route('inicio') }}" class="sitio-logo text-center text-lg-start">
                @if (! empty($config['sitio_logo']))
                    <img src="{{ Storage::disk('public')->url($config['sitio_logo']) }}" alt="{{ $sitioNombre }}" height="64">
                @else
                    <span class="sitio-logo-texto">{{ $sitioNombre }}</span>
                    <span class="sitio-logo-eslogan">{{ $config['sitio_eslogan'] ?? '' }}</span>
                @endif
            </a>

            {{-- Banner del header --}}
            @include('publico.partials.publicidad', ['banners' => $bannersHeader, 'clase' => 'sitio-publicidad-header'])
        </div>
    </header>

    {{-- ==================== Menú ==================== --}}
    <nav class="navbar navbar-expand-lg navbar-dark sitio-nav sticky-top">
        <div class="container">
            <a class="navbar-brand d-lg-none fw-bold" href="{{ route('inicio') }}">{{ $sitioNombre }}</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inicio') ? 'active' : '' }}" href="{{ route('inicio') }}">
                            <i class="bi bi-house-door-fill"></i><span class="d-lg-none ms-2">Inicio</span>
                        </a>
                    </li>
                    @foreach ($categoriasMenu as $cat)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('seccion/' . $cat->slug) ? 'active' : '' }}"
                               href="{{ route('seccion', $cat) }}">{{ $cat->nombre }}</a>
                        </li>
                    @endforeach
                </ul>

                {{-- Buscador --}}
                <form action="{{ route('buscar') }}" method="GET" role="search" class="sitio-buscador d-flex py-2 py-lg-0">
                    <input type="search" name="q" value="{{ request()->routeIs('buscar') ? request('q') : '' }}"
                           class="form-control form-control-sm" placeholder="Buscar..." aria-label="Buscar" minlength="3" maxlength="100" required>
                    <button class="btn btn-sm btn-light ms-1" aria-label="Buscar"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </nav>

    {{-- ==================== Último momento ==================== --}}
    @if ($ultimoMomento->isNotEmpty())
        <div class="sitio-ticker">
            <div class="container d-flex align-items-center">
                <span class="sitio-ticker-etiqueta">Último momento</span>
                <div class="sitio-ticker-ventana">
                    <div class="sitio-ticker-cinta">
                        {{-- Se repite dos veces para que el desplazamiento sea continuo --}}
                        @foreach ([1, 2] as $vuelta)
                            @foreach ($ultimoMomento as $nota)
                                <a href="{{ route('nota', $nota) }}">
                                    <strong>{{ $nota->publicada_at->format('H:i') }}</strong> {{ $nota->titulo }}
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== Contenido ==================== --}}
    <main class="py-4">
        @yield('contenido')
    </main>

    {{-- Publicidad arriba del pie --}}
    <div class="container">
        @include('publico.partials.publicidad', ['banners' => $bannersFooter, 'clase' => 'mt-2'])
    </div>

    {{-- ==================== Pie de página ==================== --}}
    <footer class="sitio-footer">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a href="{{ route('inicio') }}" class="sitio-logo-texto text-white d-inline-block mb-2">{{ $sitioNombre }}</a>
                    <p class="text-white-50 small mb-3">{{ $config['sitio_eslogan'] ?? '' }}</p>
                    <div class="d-flex gap-3 fs-5">
                        @foreach ($redes as $clave => [$icono, $nombre])
                            @if (! empty($config[$clave]))
                                <a href="{{ $config[$clave] }}" target="_blank" rel="noopener" title="{{ $nombre }}"><i class="bi bi-{{ $icono }}"></i></a>
                            @endif
                        @endforeach
                        @if ($whatsapp)
                            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        @endif
                    </div>
                </div>

                <div class="col-6 col-lg-4">
                    <h2 class="sitio-footer-titulo">Secciones</h2>
                    <ul class="list-unstyled sitio-footer-menu">
                        <li><a href="{{ route('inicio') }}">Inicio</a></li>
                        @foreach ($categoriasMenu as $cat)
                            <li><a href="{{ route('seccion', $cat) }}">{{ $cat->nombre }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-6 col-lg-4 small">
                    <h2 class="sitio-footer-titulo">Datos legales</h2>
                    <ul class="list-unstyled text-white-50 sitio-footer-legales">
                        @if (! empty($config['propietario']))
                            <li><span>Propietario:</span> {{ $config['propietario'] }}</li>
                        @endif
                        @if (! empty($config['director']))
                            <li><span>Director:</span> {{ $config['director'] }}</li>
                        @endif
                        @if (! empty($config['direccion']))
                            <li><span>Domicilio:</span> {{ $config['direccion'] }}</li>
                        @endif
                        @if ($whatsapp)
                            <li><span>WhatsApp:</span> +{{ $whatsapp }}</li>
                        @endif
                        @if (! empty($config['email_contacto']))
                            <li><span>Email:</span> <a href="mailto:{{ $config['email_contacto'] }}">{{ $config['email_contacto'] }}</a></li>
                        @endif
                        @if (! empty($config['registro_dnda']))
                            <li><span>Registro DNDA:</span> {{ $config['registro_dnda'] }}</li>
                        @endif
                        <li class="text-capitalize"><span>Edición:</span> {{ now()->translatedFormat('l j \d\e F Y') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="sitio-footer-copy">
            <div class="container py-3 small text-white-50 d-flex flex-wrap justify-content-between gap-2">
                <span>© {{ now()->year }} {{ $sitioNombre }}. Todos los derechos reservados.</span>
                <a href="{{ route('admin.login') }}" class="text-white-50">Acceso redacción</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
