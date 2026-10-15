<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Models\Publicidad;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotaController extends Controller
{
    public function show(Request $request, Noticia $noticia): View
    {
        $esStaff = $request->user()?->can('noticias.ver') ?? false;
        $esPublica = $noticia->estado === Noticia::ESTADO_PUBLICADA
            && $noticia->publicada_at
            && $noticia->publicada_at->lte(now());

        // Borradores y programadas: solo los ve la redacción (como vista previa)
        abort_unless($esPublica || $esStaff, 404);

        $noticia->load(['categoria', 'autor', 'tags']);

        if ($esPublica && ! $esStaff) {
            $this->contarVisita($request, $noticia);
        }

        return view('publico.nota', [
            'noticia'      => $noticia,
            'vistaPrevia'  => ! $esPublica,
            'relacionadas' => $this->relacionadas($noticia),
            'videoId'      => $this->idYoutube($noticia->video_url),
            'minutos'      => $this->minutosDeLectura($noticia->cuerpo),
            'bannersNota'  => Publicidad::vigentes()->enPosicion('nota')->get(),
        ]);
    }

    /**
     * Una visita por nota y por sesión, y sin contar robots (Google, Facebook, etc.).
     */
    private function contarVisita(Request $request, Noticia $noticia): void
    {
        $agente = (string) $request->userAgent();
        if ($agente === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|preview|curl|wget/i', $agente)) {
            return;
        }

        $clave = 'nota_vista_' . $noticia->id;
        if ($request->session()->has($clave)) {
            return;
        }

        $request->session()->put($clave, true);
        $noticia->registrarVisita();
    }

    /**
     * Notas relacionadas: primero las que comparten etiquetas, después las de la misma sección.
     */
    private function relacionadas(Noticia $noticia, int $cantidad = 4)
    {
        $tagIds = $noticia->tags->pluck('id');

        $porTags = $tagIds->isEmpty() ? collect() : Noticia::publicadas()
            ->where('id', '!=', $noticia->id)
            ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
            ->with('categoria')
            ->recientes()
            ->take($cantidad)
            ->get();

        if ($porTags->count() >= $cantidad) {
            return $porTags;
        }

        $porSeccion = Noticia::publicadas()
            ->where('id', '!=', $noticia->id)
            ->whereNotIn('id', $porTags->pluck('id'))
            ->deCategoria($noticia->categoria_id)
            ->with('categoria')
            ->recientes()
            ->take($cantidad - $porTags->count())
            ->get();

        return $porTags->concat($porSeccion);
    }

    /** Extrae el ID de un link de YouTube (watch, youtu.be, shorts, embed). */
    private function idYoutube(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([\w-]{11})~', $url, $coincidencia);

        return $coincidencia[1] ?? null;
    }

    private function minutosDeLectura(?string $html): int
    {
        $palabras = str_word_count(Str::ascii(strip_tags((string) $html)));

        return max(1, (int) ceil($palabras / 200));
    }
}
