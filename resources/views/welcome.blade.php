<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Prueba de instalación</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Deportes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Política</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Policiales</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <img src="https://picsum.photos/1200/675" class="img-nota mb-3" alt="Imagen de prueba">
                <span class="volanta">Información general</span>
                <h1 class="titulo-nota mt-1"><a href="#">El proyecto Laravel quedó instalado correctamente</a></h1>
                <p class="lead text-secondary">
                    Si ves esta página con el menú azul, la tipografía con serif en el título
                    y los íconos <i class="bi bi-check-circle-fill text-success"></i>, Bootstrap, Sass y Vite están funcionando.
                </p>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#prueba">
                    <i class="bi bi-lightning-charge-fill"></i> Probar JS de Bootstrap
                </button>
            </div>
        </div>
    </main>

    <div class="modal fade" id="prueba" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Todo OK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">El JavaScript de Bootstrap funciona. Podés pasar al Módulo 2.</div>
            </div>
        </div>
    </div>
</body>
</html>
