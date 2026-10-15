@extends('layouts.admin')

@section('titulo', 'Usuarios')

@section('contenido')

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <p class="text-secondary mb-0">Las personas que pueden entrar al panel y con qué permisos.</p>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Nuevo usuario
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th class="d-none d-md-table-cell">Firma en las notas</th>
                        <th class="text-center d-none d-sm-table-cell">Notas</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        @php
                            $rol = $usuario->getRoleNames()->first();
                            $colorRol = ['admin' => 'danger', 'editor' => 'primary', 'redactor' => 'secondary'][$rol] ?? 'light';
                        @endphp
                        <tr class="{{ $usuario->activo ? '' : 'opacity-50' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="admin-avatar">{{ $usuario->getIniciales() }}</span>
                                    <div>
                                        <div class="fw-semibold">
                                            {{ $usuario->name }}
                                            @if ($usuario->is(auth()->user()))
                                                <small class="text-secondary">(vos)</small>
                                            @endif
                                        </div>
                                        <small class="text-secondary">{{ $usuario->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge text-bg-{{ $colorRol }}">{{ $usuario->rol_nombre }}</span></td>
                            <td class="d-none d-md-table-cell">{{ $usuario->nombre_firma }}</td>
                            <td class="text-center d-none d-sm-table-cell">{{ $usuario->noticias_count }}</td>
                            <td>
                                @if ($usuario->activo)
                                    <span class="badge text-bg-success">Activo</span>
                                @else
                                    <span class="badge text-bg-secondary">Desactivado</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                @unless ($usuario->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar a {{ $usuario->name }}? Si tiene notas, se va a desactivar en lugar de eliminarse.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Referencia de permisos --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-shield-check me-1"></i> Qué puede hacer cada rol</div>
        <div class="card-body small">
            <p class="mb-2"><span class="badge text-bg-danger">Administrador</span> Todo: noticias, secciones, publicidades, cotizaciones, usuarios y configuración.</p>
            <p class="mb-2"><span class="badge text-bg-primary">Editor</span> Crea, edita, publica y elimina cualquier nota. Maneja secciones y publicidades.</p>
            <p class="mb-0"><span class="badge text-bg-secondary">Redactor</span> Crea y edita solo sus notas, que quedan en borrador hasta que un editor las publica.</p>
        </div>
    </div>

@endsection
