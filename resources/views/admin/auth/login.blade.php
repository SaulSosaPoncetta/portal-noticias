<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Ingresar · {{ \App\Models\Configuracion::valor('sitio_nombre', config('app.name')) }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="admin-login d-flex align-items-center min-vh-100 py-4">

<main class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">

            <div class="text-center mb-4">
                <h1 class="titulo-nota h3 mb-1 text-white">
                    {{ \App\Models\Configuracion::valor('sitio_nombre', config('app.name')) }}
                </h1>
                <p class="text-white-50 mb-0">Panel de administración</p>
            </div>

            <div class="card shadow-lg border-0">
                <div class="card-body p-4">

                    @if (session('ok'))
                        <div class="alert alert-success py-2">{{ session('ok') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.post') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       autocomplete="username" autofocus required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="current-password" required>
                                <button type="button" class="btn btn-outline-secondary" id="verPassword" title="Mostrar contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="recordar" id="recordar" value="1">
                            <label class="form-check-label" for="recordar">Mantener la sesión iniciada</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center mt-3 mb-0">
                <a href="{{ route('inicio') }}" class="text-white-50 small"><i class="bi bi-arrow-left"></i> Volver al sitio</a>
            </p>
        </div>
    </div>
</main>

<script>
    document.getElementById('verPassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        this.innerHTML = visible ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
</script>
</body>
</html>
