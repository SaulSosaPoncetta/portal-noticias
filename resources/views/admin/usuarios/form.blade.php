@extends('layouts.admin')

@section('titulo', $usuario->exists ? 'Editar usuario' : 'Nuevo usuario')

@section('contenido')

    @php $esUnoMismo = $usuario->exists && $usuario->is(auth()->user()); @endphp

    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <form method="POST" class="card border-0 shadow-sm"
                  action="{{ $usuario->exists ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}">
                @csrf
                @if ($usuario->exists)
                    @method('PUT')
                @endif

                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="name" class="form-label">Nombre y apellido <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" maxlength="120" value="{{ old('name', $usuario->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="firma" class="form-label">Firma en las notas</label>
                            <input type="text" name="firma" id="firma" maxlength="120" value="{{ old('firma', $usuario->firma) }}"
                                   class="form-control @error('firma') is-invalid @enderror" placeholder="Ej: Redacción">
                            <div class="form-text">Vacío = se usa el nombre.</div>
                            @error('firma') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" maxlength="190" value="{{ old('email', $usuario->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" autocomplete="off" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="password" class="form-label">
                                Contraseña @unless ($usuario->exists) <span class="text-danger">*</span> @endunless
                            </label>
                            <input type="password" name="password" id="password" autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            <div class="form-text">
                                {{ $usuario->exists ? 'Dejala vacía para no cambiarla.' : 'Mínimo 8 caracteres, con letras y números.' }}
                            </div>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="password_confirmation" class="form-label">Repetir contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="form-control">
                        </div>

                        <div class="col-sm-6">
                            <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                            @if ($esUnoMismo)
                                <input type="hidden" name="rol" value="{{ $rolActual }}">
                                <input type="text" class="form-control" value="{{ \App\Models\User::ROLES[$rolActual] ?? $rolActual }}" disabled>
                                <div class="form-text">No podés cambiar tu propio rol.</div>
                            @else
                                <select name="rol" id="rol" class="form-select @error('rol') is-invalid @enderror">
                                    @foreach (\App\Models\User::ROLES as $clave => $texto)
                                        <option value="{{ $clave }}" @selected(old('rol', $rolActual) === $clave)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                                @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @endif
                        </div>

                        <div class="col-sm-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="activo" id="activo" value="1"
                                       @checked(old('activo', $usuario->activo)) @disabled($esUnoMismo)>
                                <label class="form-check-label" for="activo">Puede entrar al panel</label>
                                @if ($esUnoMismo)
                                    <input type="hidden" name="activo" value="1">
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="bio" class="form-label">Biografía breve</label>
                            <textarea name="bio" id="bio" rows="2" maxlength="500"
                                      class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $usuario->bio) }}</textarea>
                            @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@endsection
