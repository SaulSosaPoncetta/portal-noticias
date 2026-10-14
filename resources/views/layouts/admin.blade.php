<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo', 'Panel') · {{ \App\Models\Configuracion::valor('sitio_nombre', config('app.name')) }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('estilos')
</head>
<body class="admin-body">

@php
    /**
     * Menú lateral. Cada ítem se muestra solo si el usuario tiene el permiso
     * y si la ruta ya existe (las secciones se van sumando en los próximos módulos).
     */
    $menu = [
        ['ruta' => 'admin.dashboard',          'icono' => 'speedometer2',  'texto' => 'Inicio',        'permiso' => null],
        ['ruta' => 'admin.noticias.index',     'icono' => 'newspaper',     'texto' => 'Noticias',      'permiso' => 'noticias.ver'],
        ['ruta' => 'admin.categorias.index',   'icono' => 'tags',          'texto' => 'Secciones',     'permiso' => 'categorias.gestionar'],
        ['ruta' => 'admin.publicidades.index', 'icono' => 'megaphone',     'texto' => 'Publicidades',  'permiso' => 'publicidades.gestionar'],
        ['ruta' => 'admin.cotizaciones.index', 'icono' => 'currency-dollar','texto' => 'Cotizaciones', 'permiso' => 'cotizaciones.gestionar'],
        ['ruta' => 'admin.usuarios.index',     'icono' => 'people',        'texto' => 'Usuarios',      'permiso' => 'usuarios.gestionar'],
        ['ruta' => 'admin.configuracion.index','icono' => 'gear',          'texto' => 'Configuración', 'permiso' => 'configuracion.gestionar'],
    ];
@endphp

<div class="d-flex">

    {{-- Barra lateral (offcanvas en celulares, fija en escritorio) --}}
    <aside class="offcanvas-lg offcanvas-start admin-sidebar bg-dark text-white" tabindex="-1" id="sidebar">
        <div class="offcanvas-header border-bottom border-secondary">
            <span class="fw-bold">{{ \App\Models\Configuracion::valor('sitio_nombre', config('app.name')) }}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
        </div>

        <div class="offcanvas-body d-flex flex-column p-0">
            <a href="{{ route('admin.dashboard') }}" class="d-none d-lg-block px-3 py-3 border-bottom border-secondary text-white text-decoration-none">
                <span class="fw-bold fs-5">{{ \App\Models\Configuracion::valor('sitio_nombre', config('app.name')) }}</span>
                <small class="d-block text-white-50">Panel de administración</small>
            </a>

            <nav class="nav flex-column py-2">
                @foreach ($menu as $item)
                    @continue($item['permiso'] && ! auth()->user()->can($item['permiso']))
                    @if (Route::has($item['ruta']))
                        <a href="{{ route($item['ruta']) }}"
                           class="nav-link admin-nav-link {{ request()->routeIs(str_replace('.index', '.*', $item['ruta'])) ? 'active' : '' }}">
                            <i class="bi bi-{{ $item['icono'] }} me-2"></i>{{ $item['texto'] }}
                        </a>
                    @else
                        <span class="nav-link admin-nav-link disabled" title="Disponible en los próximos módulos">
                            <i class="bi bi-{{ $item['icono'] }} me-2"></i>{{ $item['texto'] }}
                            <small class="badge text-bg-secondary ms-1">pronto</small>
                        </span>
                    @endif
                @endforeach
            </nav>

            <div class="mt-auto p-3 border-top border-secondary">
                <a href="{{ route('inicio') }}" target="_blank" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver el sitio
                </a>
            </div>
        </div>
    </aside>

    {{-- Contenido --}}
    <div class="flex-grow-1 min-vw-0">
        <header class="navbar bg-white border-bottom px-3 sticky-top">
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <i class="bi bi-list"></i>
            </button>

            <h1 class="h5 mb-0 ms-2 ms-lg-0">@yield('titulo', 'Panel')</h1>

            <div class="dropdown ms-auto">
                <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <span class="admin-avatar">{{ auth()->user()->getIniciales() }}</span>
                    <span class="d-none d-sm-inline text-start lh-sm">
                        {{ auth()->user()->name }}<br>
                        <small class="text-secondary">{{ auth()->user()->rol_nombre }}</small>
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="p-3 p-lg-4">
            @if (session('ok'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-1"></i> {{ session('ok') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('contenido')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
