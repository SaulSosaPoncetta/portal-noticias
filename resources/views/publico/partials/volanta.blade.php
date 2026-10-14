{{-- Etiqueta de sección (o volanta propia de la nota) con el color de la sección --}}
<a href="{{ route('seccion', $nota->categoria) }}" class="volanta" style="color: {{ $nota->categoria->color }}">
    {{ $nota->volanta ?: $nota->categoria->nombre }}
</a>
