@extends('layouts.publico')

@section('titulo', 'En construcción')

@section('contenido')
<div class="container text-center py-5">
    <i class="bi bi-cone-striped display-3 text-warning d-block mb-3"></i>
    <h1 class="titulo-nota h3">Esta página se completa en el Módulo 9</h1>
    <p class="text-secondary">Acá se van a ver la nota completa y el listado de cada sección.</p>
    <a href="{{ route('inicio') }}" class="btn btn-primary mt-2"><i class="bi bi-arrow-left me-1"></i> Volver a la portada</a>
</div>
@endsection
