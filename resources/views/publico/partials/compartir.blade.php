{{-- Botones para compartir la nota --}}
@php
    $urlNota = route('nota', $noticia);
    $texto = $noticia->titulo;
    $enlaces = [
        ['whatsapp',  'WhatsApp',   'https://api.whatsapp.com/send?text=' . rawurlencode($texto . ' ' . $urlNota), 'compartir-whatsapp'],
        ['facebook',  'Facebook',   'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($urlNota),       'compartir-facebook'],
        ['twitter-x', 'X',          'https://x.com/intent/post?url=' . rawurlencode($urlNota) . '&text=' . rawurlencode($texto), 'compartir-x'],
        ['telegram',  'Telegram',   'https://t.me/share/url?url=' . rawurlencode($urlNota) . '&text=' . rawurlencode($texto), 'compartir-telegram'],
    ];
@endphp

<div class="compartir d-flex flex-wrap align-items-center gap-2">
    <span class="small text-secondary me-1">Compartir</span>

    @foreach ($enlaces as [$icono, $nombre, $href, $clase])
        <a href="{{ $href }}" target="_blank" rel="noopener" class="btn btn-sm compartir-btn {{ $clase }}" title="Compartir en {{ $nombre }}">
            <i class="bi bi-{{ $icono }}"></i>
        </a>
    @endforeach

    <button type="button" class="btn btn-sm compartir-btn compartir-copiar" data-url="{{ $urlNota }}" title="Copiar link">
        <i class="bi bi-link-45deg"></i>
    </button>

    {{-- En celulares aparece el menú nativo de compartir --}}
    <button type="button" class="btn btn-sm compartir-btn compartir-nativo d-none" data-url="{{ $urlNota }}" data-titulo="{{ $texto }}" title="Más opciones">
        <i class="bi bi-share"></i>
    </button>
</div>

@once
    @push('scripts')
    <script>
        document.querySelectorAll('.compartir-copiar').forEach((boton) => {
            boton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(boton.dataset.url);
                    const icono = boton.innerHTML;
                    boton.innerHTML = '<i class="bi bi-check-lg"></i>';
                    setTimeout(() => boton.innerHTML = icono, 1500);
                } catch (e) {
                    prompt('Copiá el link:', boton.dataset.url);
                }
            });
        });

        if (navigator.share) {
            document.querySelectorAll('.compartir-nativo').forEach((boton) => {
                boton.classList.remove('d-none');
                boton.addEventListener('click', () => {
                    navigator.share({ title: boton.dataset.titulo, url: boton.dataset.url }).catch(() => {});
                });
            });
        }
    </script>
    @endpush
@endonce
