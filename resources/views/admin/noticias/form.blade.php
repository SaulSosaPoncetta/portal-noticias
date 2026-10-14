@extends('layouts.admin')

@section('titulo', $noticia->exists ? 'Editar nota' : 'Nueva nota')

@push('estilos')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <style>
        #editor { min-height: 380px; font-size: 1.05rem; background: #fff; }
        #editor .ql-editor { min-height: 380px; }
        .ql-toolbar.ql-snow { background: #fff; border-top-left-radius: .375rem; border-top-right-radius: .375rem; }
        .ql-container.ql-snow { border-bottom-left-radius: .375rem; border-bottom-right-radius: .375rem; }
        .vista-previa-img { width: 100%; aspect-ratio: 16/9; object-fit: cover; border-radius: .375rem; background: #e4e7ec; }
    </style>
@endpush

@section('contenido')

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i> Revisá los campos marcados en rojo.
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" id="formNoticia"
          action="{{ $noticia->exists ? route('admin.noticias.update', $noticia) : route('admin.noticias.store') }}">
        @csrf
        @if ($noticia->exists)
            @method('PUT')
        @endif

        <div class="row g-4">

            {{-- ==================== Columna principal ==================== --}}
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <div class="mb-3">
                            <label for="volanta" class="form-label">Volanta</label>
                            <input type="text" name="volanta" id="volanta" maxlength="80"
                                   value="{{ old('volanta', $noticia->volanta) }}"
                                   class="form-control @error('volanta') is-invalid @enderror"
                                   placeholder="Texto corto arriba del título (ej: Concejo Deliberante)">
                            @error('volanta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label d-flex justify-content-between">
                                <span>Título <span class="text-danger">*</span></span>
                                <small class="text-secondary"><span id="contTitulo">0</span>/200</small>
                            </label>
                            <textarea name="titulo" id="titulo" rows="2" maxlength="200"
                                      class="form-control form-control-lg titulo-nota @error('titulo') is-invalid @enderror"
                                      required>{{ old('titulo', $noticia->titulo) }}</textarea>
                            @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($noticia->exists)
                                <div class="form-text">URL: <code>/nota/{{ $noticia->slug }}</code></div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="bajada" class="form-label d-flex justify-content-between">
                                <span>Bajada</span>
                                <small class="text-secondary"><span id="contBajada">0</span>/600</small>
                            </label>
                            <textarea name="bajada" id="bajada" rows="3" maxlength="600"
                                      class="form-control @error('bajada') is-invalid @enderror"
                                      placeholder="Resumen que se muestra debajo del título">{{ old('bajada', $noticia->bajada) }}</textarea>
                            @error('bajada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-1">
                            <label class="form-label">Cuerpo de la nota <span class="text-danger">*</span></label>
                            <div id="editor">{!! old('cuerpo', $noticia->cuerpo) !!}</div>
                            <input type="hidden" name="cuerpo" id="cuerpo">
                            @error('cuerpo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <div class="form-text">Para insertar fotos usá el botón de imagen de la barra; para videos de YouTube, el botón de video.</div>
                        </div>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <a class="text-decoration-none fw-semibold d-flex justify-content-between" data-bs-toggle="collapse" href="#seo">
                            <span><i class="bi bi-google me-1"></i> SEO y redes (opcional)</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                    </div>
                    <div class="collapse {{ $errors->hasAny(['meta_titulo', 'meta_descripcion']) ? 'show' : '' }}" id="seo">
                        <div class="card-body">
                            <p class="small text-secondary">Si los dejás vacíos se usan el título y la bajada.</p>
                            <div class="mb-3">
                                <label for="meta_titulo" class="form-label">Título para Google</label>
                                <input type="text" name="meta_titulo" id="meta_titulo" maxlength="255"
                                       value="{{ old('meta_titulo', $noticia->meta_titulo) }}" class="form-control @error('meta_titulo') is-invalid @enderror">
                                @error('meta_titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label for="meta_descripcion" class="form-label">Descripción para Google</label>
                                <textarea name="meta_descripcion" id="meta_descripcion" rows="2" maxlength="255"
                                          class="form-control @error('meta_descripcion') is-invalid @enderror">{{ old('meta_descripcion', $noticia->meta_descripcion) }}</textarea>
                                @error('meta_descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== Columna lateral ==================== --}}
            <div class="col-xl-4">

                {{-- Publicación --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-send me-1"></i> Publicación</div>
                    <div class="card-body">

                        @if ($puedePublicar)
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror">
                                    <option value="borrador" @selected(old('estado', $noticia->estado) === 'borrador')>Borrador (no se ve en el sitio)</option>
                                    <option value="publicada" @selected(old('estado', $noticia->estado) === 'publicada')>Publicada</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="publicada_at" class="form-label">Fecha y hora de publicación</label>
                                <input type="datetime-local" name="publicada_at" id="publicada_at"
                                       value="{{ old('publicada_at', $noticia->publicada_at?->format('Y-m-d\TH:i')) }}"
                                       class="form-control @error('publicada_at') is-invalid @enderror">
                                <div class="form-text">Si ponés una fecha futura, la nota queda programada.</div>
                                @error('publicada_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="destacada" id="destacada" value="1"
                                       @checked(old('destacada', $noticia->destacada))>
                                <label class="form-check-label" for="destacada"><i class="bi bi-star-fill text-warning"></i> Destacada (candidata a nota principal)</label>
                            </div>

                            <div>
                                <label for="orden_portada" class="form-label small mb-1">Posición fija en portada (opcional)</label>
                                <input type="number" name="orden_portada" id="orden_portada" min="1" max="99"
                                       value="{{ old('orden_portada', $noticia->orden_portada) }}"
                                       class="form-control form-control-sm @error('orden_portada') is-invalid @enderror" placeholder="Vacío = por fecha">
                                @error('orden_portada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <input type="hidden" name="estado" value="borrador">
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                La nota se guarda como <strong>borrador</strong>. Un editor la va a revisar y publicar.
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                        <a href="{{ route('admin.noticias.index') }}" class="btn btn-outline-secondary">Volver</a>
                    </div>
                </div>

                {{-- Sección y tags --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Sección <span class="text-danger">*</span></label>
                            <select name="categoria_id" id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                                <option value="">Elegí una sección</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('categoria_id', $noticia->categoria_id) == $cat->id)>{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label for="tags" class="form-label">Etiquetas</label>
                            <input type="text" name="tags" id="tags" maxlength="500"
                                   value="{{ old('tags', $noticia->exists ? $noticia->tags->pluck('nombre')->implode(', ') : '') }}"
                                   class="form-control @error('tags') is-invalid @enderror" placeholder="Separadas por coma">
                            <div class="form-text">Ej: Fiesta aniversario, Cultura. Máximo 10.</div>
                            @error('tags') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Imagen principal --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-image me-1"></i> Imagen principal</div>
                    <div class="card-body">
                        <img src="{{ $noticia->url_imagen }}" alt="" id="vistaPrevia" class="vista-previa-img mb-3">

                        <input type="file" name="imagen" id="imagen" accept="image/jpeg,image/png,image/webp"
                               class="form-control @error('imagen') is-invalid @enderror">
                        <div class="form-text">JPG, PNG o WEBP, hasta 4 MB. Ideal: 1200 × 675 px (16:9).</div>
                        @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        @if ($noticia->imagen)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="quitar_imagen" id="quitar_imagen" value="1">
                                <label class="form-check-label small text-danger" for="quitar_imagen">Quitar la imagen actual</label>
                            </div>
                        @endif

                        <div class="mt-3">
                            <label for="epigrafe" class="form-label">Epígrafe</label>
                            <input type="text" name="epigrafe" id="epigrafe" maxlength="255"
                                   value="{{ old('epigrafe', $noticia->epigrafe) }}"
                                   class="form-control @error('epigrafe') is-invalid @enderror" placeholder="Pie de foto">
                            @error('epigrafe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Video --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <label for="video_url" class="form-label"><i class="bi bi-youtube text-danger me-1"></i> Video destacado (opcional)</label>
                        <input type="url" name="video_url" id="video_url" maxlength="255"
                               value="{{ old('video_url', $noticia->video_url) }}"
                               class="form-control @error('video_url') is-invalid @enderror" placeholder="https://www.youtube.com/watch?v=...">
                        @error('video_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if ($noticia->exists)
        @can('delete', $noticia)
            <form method="POST" action="{{ route('admin.noticias.destroy', $noticia) }}" class="mt-4"
                  onsubmit="return confirm('¿Enviar esta nota a la papelera?')">
                @csrf @method('DELETE')
                <button class="btn btn-link text-danger px-0"><i class="bi bi-trash me-1"></i> Enviar a la papelera</button>
            </form>
        @endcan
    @endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    // ---------- Editor de texto ----------
    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Escribí la nota...',
        modules: {
            toolbar: {
                container: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'link'],
                    ['image', 'video'],
                    ['clean'],
                ],
                handlers: { image: subirImagenEditor },
            },
        },
    });

    // Las imágenes del cuerpo se suben al servidor (no se guardan en base64)
    function subirImagenEditor() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/jpeg,image/png,image/webp,image/gif';

        input.onchange = async () => {
            if (!input.files.length) return;

            const datos = new FormData();
            datos.append('imagen', input.files[0]);

            const respuesta = await fetch(@json(route('admin.noticias.subir-imagen')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: datos,
            });

            if (!respuesta.ok) {
                alert('No se pudo subir la imagen. Verificá que sea JPG, PNG, WEBP o GIF de hasta 4 MB.');
                return;
            }

            const { url } = await respuesta.json();
            const rango = quill.getSelection(true);
            quill.insertEmbed(rango.index, 'image', url, 'user');
            quill.setSelection(rango.index + 1);
        };

        input.click();
    }

    // Al guardar, el HTML del editor pasa al campo oculto
    document.getElementById('formNoticia').addEventListener('submit', () => {
        document.getElementById('cuerpo').value = quill.root.innerHTML;
    });

    // ---------- Vista previa de la imagen principal ----------
    document.getElementById('imagen').addEventListener('change', (e) => {
        const archivo = e.target.files[0];
        if (archivo) {
            document.getElementById('vistaPrevia').src = URL.createObjectURL(archivo);
        }
    });

    // ---------- Contadores de caracteres ----------
    [['titulo', 'contTitulo'], ['bajada', 'contBajada']].forEach(([campo, contador]) => {
        const el = document.getElementById(campo);
        const actualizar = () => document.getElementById(contador).textContent = el.value.length;
        el.addEventListener('input', actualizar);
        actualizar();
    });
</script>
@endpush
