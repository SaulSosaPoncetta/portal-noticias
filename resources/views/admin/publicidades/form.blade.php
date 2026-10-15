@extends('layouts.admin')

@section('titulo', $publicidad->exists ? 'Editar publicidad' : 'Nueva publicidad')

@section('contenido')

    <form method="POST" enctype="multipart/form-data"
          action="{{ $publicidad->exists ? route('admin.publicidades.update', $publicidad) : route('admin.publicidades.store') }}">
        @csrf
        @if ($publicidad->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Anunciante <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" maxlength="120" value="{{ old('nombre', $publicidad->nombre) }}"
                                   class="form-control @error('nombre') is-invalid @enderror" required autofocus>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-8">
                                <label for="posicion" class="form-label">Dónde se muestra <span class="text-danger">*</span></label>
                                <select name="posicion" id="posicion" class="form-select @error('posicion') is-invalid @enderror">
                                    @foreach (\App\Models\Publicidad::POSICIONES as $clave => $texto)
                                        <option value="{{ $clave }}" @selected(old('posicion', $publicidad->posicion) === $clave)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                                @error('posicion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-4">
                                <label for="orden" class="form-label">Orden</label>
                                <input type="number" name="orden" id="orden" min="0" max="999" value="{{ old('orden', $publicidad->orden) }}"
                                       class="form-control @error('orden') is-invalid @enderror">
                                @error('orden') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label">Link al hacer clic</label>
                            <input type="url" name="url" id="url" maxlength="255" value="{{ old('url', $publicidad->url) }}"
                                   class="form-control @error('url') is-invalid @enderror" placeholder="https://...">
                            <div class="form-text">Opcional. Se cuentan los clics.</div>
                            @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="desde" class="form-label">Desde</label>
                                <input type="date" name="desde" id="desde" value="{{ old('desde', $publicidad->desde?->format('Y-m-d')) }}"
                                       class="form-control @error('desde') is-invalid @enderror">
                                @error('desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="hasta" class="form-label">Hasta</label>
                                <input type="date" name="hasta" id="hasta" value="{{ old('hasta', $publicidad->hasta?->format('Y-m-d')) }}"
                                       class="form-control @error('hasta') is-invalid @enderror">
                                @error('hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 form-text mt-1">Vacío = sin límite. Al vencer, el banner deja de mostrarse solo.</div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="activa" id="activa" value="1"
                                   @checked(old('activa', $publicidad->activa))>
                            <label class="form-check-label" for="activa">Activa</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-display me-1"></i> Imagen (computadora) {!! $publicidad->exists ? '' : '<span class="text-danger">*</span>' !!}</div>
                    <div class="card-body">
                        <img src="{{ $publicidad->imagen ? $publicidad->url_imagen : asset('img/sin-imagen.svg') }}" id="previa" alt="" class="img-fluid rounded border mb-3 w-100">
                        <input type="file" name="imagen" id="imagen" accept="image/*" class="form-control @error('imagen') is-invalid @enderror">
                        <div class="form-text">JPG, PNG, WEBP o GIF animado. Hasta 3 MB.</div>
                        @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-phone me-1"></i> Imagen para celular (opcional)</div>
                    <div class="card-body">
                        @if ($publicidad->imagen_mobile)
                            <img src="{{ $publicidad->url_imagen_mobile }}" alt="" class="img-fluid rounded border mb-2">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="quitar_imagen_mobile" id="quitar_imagen_mobile" value="1">
                                <label class="form-check-label small text-danger" for="quitar_imagen_mobile">Quitar la imagen para celular</label>
                            </div>
                        @endif
                        <input type="file" name="imagen_mobile" accept="image/*" class="form-control @error('imagen_mobile') is-invalid @enderror">
                        <div class="form-text">Si no la cargás, en el celular se usa la de computadora.</div>
                        @error('imagen_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            <a href="{{ route('admin.publicidades.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    document.getElementById('imagen').addEventListener('change', (e) => {
        if (e.target.files[0]) document.getElementById('previa').src = URL.createObjectURL(e.target.files[0]);
    });
</script>
@endpush
