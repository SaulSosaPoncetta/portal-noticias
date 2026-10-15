@extends('layouts.admin')

@section('titulo', 'Configuración del sitio')

@section('contenido')

    @if ($errors->any())
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Revisá los campos marcados en rojo.</div>
    @endif

    <form method="POST" action="{{ route('admin.configuracion.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-4">

            {{-- Logo --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-image me-1"></i> Logo</div>
                    <div class="card-body d-flex flex-wrap align-items-center gap-4">
                        <div class="border rounded p-3 bg-light text-center" style="min-width: 220px">
                            @if ($logo)
                                <img src="{{ Storage::disk('public')->url($logo) }}" alt="Logo" style="max-height: 70px; max-width: 260px">
                            @else
                                <span class="text-secondary small">Sin logo: se muestra el nombre del diario</span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="form-control @error('logo') is-invalid @enderror" style="max-width: 420px">
                            <div class="form-text">PNG con fondo transparente o SVG, hasta 1 MB. Altura recomendada: 128 px.</div>
                            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($logo)
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="quitar_logo" id="quitar_logo" value="1">
                                    <label class="form-check-label small text-danger" for="quitar_logo">Quitar el logo</label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grupos de configuración --}}
            @foreach ($grupos as $grupo => [$tituloGrupo, $icono])
                @continue(! $configuraciones->has($grupo))

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-semibold"><i class="bi bi-{{ $icono }} me-1"></i> {{ $tituloGrupo }}</div>
                        <div class="card-body">
                            @foreach ($configuraciones[$grupo] as $item)
                                @continue($item->clave === 'sitio_logo')

                                @php
                                    $nombreCampo = "config[{$item->clave}]";
                                    $claveError = "config.{$item->clave}";
                                    $valor = old("config.{$item->clave}", $item->valor);
                                    $esLargo = $item->clave === 'seo_descripcion';
                                    $tipo = match (true) {
                                        in_array($item->clave, ['facebook', 'instagram', 'youtube', 'x_twitter']) => 'url',
                                        $item->clave === 'email_contacto' => 'email',
                                        default => 'text',
                                    };
                                @endphp

                                <div class="mb-3">
                                    <label for="cfg_{{ $item->clave }}" class="form-label small fw-semibold mb-1">
                                        {{ $item->descripcion ?: $item->clave }}
                                    </label>
                                    @if ($esLargo)
                                        <textarea name="{{ $nombreCampo }}" id="cfg_{{ $item->clave }}" rows="3"
                                                  class="form-control @error($claveError) is-invalid @enderror">{{ $valor }}</textarea>
                                    @else
                                        <input type="{{ $tipo }}" name="{{ $nombreCampo }}" id="cfg_{{ $item->clave }}" value="{{ $valor }}"
                                               class="form-control @error($claveError) is-invalid @enderror">
                                    @endif
                                    @error($claveError) <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endforeach

                            @if ($grupo === 'clima')
                                <p class="small text-secondary mb-0">
                                    <i class="bi bi-info-circle"></i> Para obtener las coordenadas: en Google Maps hacé clic derecho sobre la ciudad
                                    y copiá los dos números (el primero es la latitud y el segundo la longitud).
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            <button class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i> Guardar configuración</button>
        </div>
    </form>

@endsection
