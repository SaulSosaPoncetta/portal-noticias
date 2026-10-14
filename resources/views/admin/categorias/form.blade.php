@extends('layouts.admin')

@section('titulo', $categoria->exists ? 'Editar sección' : 'Nueva sección')

@section('contenido')

    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST"
                          action="{{ $categoria->exists ? route('admin.categorias.update', $categoria) : route('admin.categorias.store') }}">
                        @csrf
                        @if ($categoria->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" maxlength="80"
                                   value="{{ old('nombre', $categoria->nombre) }}"
                                   class="form-control @error('nombre') is-invalid @enderror" required autofocus>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">URL</label>
                            <div class="input-group">
                                <span class="input-group-text">/seccion/</span>
                                <input type="text" name="slug" id="slug" maxlength="100"
                                       value="{{ old('slug', $categoria->slug) }}"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       placeholder="se genera desde el nombre">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @if ($categoria->exists)
                                <div class="form-text text-warning-emphasis"><i class="bi bi-exclamation-triangle"></i> Si la cambiás, los links viejos a esta sección dejan de funcionar.</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" maxlength="255"
                                   value="{{ old('descripcion', $categoria->descripcion) }}"
                                   class="form-control @error('descripcion') is-invalid @enderror">
                            <div class="form-text">Se usa para Google y para compartir en redes.</div>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="color" class="form-label">Color de la etiqueta</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="color" name="color" id="color"
                                       value="{{ old('color', $categoria->color) }}"
                                       class="form-control form-control-color @error('color') is-invalid @enderror">
                                <span class="badge fs-6" id="muestraColor" style="background: {{ old('color', $categoria->color) }}">
                                    {{ old('nombre', $categoria->nombre) ?: 'Vista previa' }}
                                </span>
                            </div>
                            @error('color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="en_menu" id="en_menu" value="1"
                                       @checked(old('en_menu', $categoria->en_menu))>
                                <label class="form-check-label" for="en_menu">Mostrar en el menú</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="en_portada" id="en_portada" value="1"
                                       @checked(old('en_portada', $categoria->en_portada))>
                                <label class="form-check-label" for="en_portada">Tiene bloque propio en la portada</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="activa" id="activa" value="1"
                                       @checked(old('activa', $categoria->activa))>
                                <label class="form-check-label" for="activa">Activa</label>
                                <div class="form-text">Si la desactivás, desaparece del sitio pero las notas se conservan.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                            <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const color = document.getElementById('color');
    const nombre = document.getElementById('nombre');
    const muestra = document.getElementById('muestraColor');
    color.addEventListener('input', () => muestra.style.background = color.value);
    nombre.addEventListener('input', () => muestra.textContent = nombre.value || 'Vista previa');
</script>
@endpush
