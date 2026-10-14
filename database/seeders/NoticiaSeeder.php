<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noticias de ejemplo para que la portada tenga contenido desde el principio.
 * Las imágenes son URLs de picsum.photos (fotos aleatorias fijas por número).
 *
 * Se puede correr varias veces: borra las notas de ejemplo anteriores y las vuelve a crear.
 */
class NoticiaSeeder extends Seeder
{
    /** Títulos inventados por sección: [volanta, título, bajada] */
    private array $titulos = [
        'Información General' => [
            ['Ciudad', 'Lanzaron la convocatoria a músicos locales para la fiesta aniversario', 'Las bandas y solistas interesados podrán inscribirse hasta fin de mes. Habrá tres escenarios y una grilla con artistas de la región.'],
            ['Educación', 'Abrió la preinscripción para las carreras de Medicina y Enfermería 2027', 'El trámite se realiza en línea y la documentación se presentará en forma presencial durante noviembre.'],
            ['Obras', 'Quedó habilitada la nueva playa de estacionamiento para camiones', 'El predio cuenta con iluminación, sanitarios y vigilancia las 24 horas, y busca descomprimir el tránsito pesado en el centro.'],
            ['Salud', 'Inscriben para el curso gratuito de manipulación de alimentos', 'La capacitación es obligatoria para quienes trabajan en comercios gastronómicos y tendrá cupos limitados.'],
            ['Clima', 'El tiempo empezará a mejorar recién el viernes', 'Se esperan lluvias aisladas y temperaturas bajas hasta el jueves. El fin de semana llegaría con sol y máximas de 22 grados.'],
            ['Comunidad', 'Con música en vivo y torta frita, el barrio Norte celebró su aniversario', 'Cientos de vecinos participaron de la jornada, que incluyó desfile de instituciones, feria de emprendedores y espectáculos.'],
            ['Servicios', 'Anuncian cortes de agua programados en tres barrios por obras', 'La cooperativa informó que los trabajos se realizarán entre las 8 y las 14 horas y recomendó hacer reserva.'],
        ],
        'Política' => [
            ['Concejo Deliberante', 'El Concejo aprobó por unanimidad el nuevo código de tránsito local', 'La ordenanza actualiza multas, regula el uso de monopatines eléctricos y crea zonas de estacionamiento medido.'],
            ['Municipio', 'El intendente presentó el presupuesto para el año próximo', 'El proyecto prioriza obras de infraestructura, salud y seguridad, y ahora será analizado por las comisiones del Concejo.'],
            ['Provincia', 'Firmaron un convenio para ampliar la red de gas natural', 'El acuerdo entre el Municipio y la Provincia permitirá que más de 800 familias accedan al servicio.'],
            ['Legislatura', 'Diputados locales impulsan una ley de promoción para pymes', 'El proyecto propone beneficios impositivos para las empresas que generen nuevos puestos de trabajo.'],
            ['Elecciones', 'Comenzó el cronograma electoral con el cierre de listas', 'Los partidos tienen plazo hasta el viernes para presentar sus candidatos ante la Junta Electoral.'],
        ],
        'Policiales' => [
            ['Tránsito', 'Chocaron dos autos y uno terminó contra una columna de alumbrado', 'El accidente ocurrió en una esquina céntrica durante la mañana. No se registraron heridos de gravedad.'],
            ['Seguridad', 'Detuvieron a un hombre que intentó ingresar por la fuerza a una vivienda', 'Los vecinos alertaron a la Policía, que llegó en pocos minutos y lo aprehendió en el lugar.'],
            ['Tránsito', 'Un motociclista resultó herido tras chocar contra un colectivo', 'Fue trasladado al hospital con lesiones leves. Se investigan las causas del siniestro.'],
            ['Bomberos', 'Tomó fuego el cielorraso de una casa quinta en las afueras', 'Los bomberos voluntarios controlaron las llamas rápidamente y no hubo personas lesionadas.'],
            ['Investigación', 'Secuestraron mercadería robada en un allanamiento', 'El procedimiento se realizó por orden de la fiscalía y permitió recuperar herramientas y electrodomésticos.'],
        ],
        'Deportes' => [
            ['Fútbol', 'El equipo local ganó el clásico y se acerca a la punta', 'Con dos goles en el segundo tiempo, se quedó con el partido más esperado de la fecha ante su gente.'],
            ['Atletismo', 'Un corredor de la ciudad ganó la ultra maratón de montaña', 'Completó los 50 kilómetros del recorrido en poco más de cinco horas y se impuso en la clasificación general.'],
            ['Fútbol femenino', 'Ganaron los dos equipos de la ciudad en la liga regional', 'Ambos conjuntos sumaron de a tres y siguen en zona de clasificación a los playoffs.'],
            ['Básquet', 'Arranca el torneo provincial con tres representantes locales', 'Los equipos debutarán como locales y esperan una buena convocatoria de público.'],
            ['Golf', 'Una joven golfista se destacó en el torneo regional', 'Terminó entre las tres mejores de su categoría y logró la clasificación al certamen nacional.'],
            ['Tenis', 'Una tenista local llegó a semifinales en un torneo internacional', 'Tras superar tres rondas, cayó en un partido muy parejo ante la primera preclasificada.'],
        ],
        'Espectáculos' => [
            ['Cultura', 'La Feria del Libro cerró su edición con récord de visitantes', 'Durante siete días hubo presentaciones, charlas y talleres en el Centro Cultural, con entrada libre y gratuita.'],
            ['Agenda', 'Una agenda cargada y variada para este fin de semana', 'Teatro, música, cine y propuestas para chicos: todo lo que se puede hacer en la ciudad.'],
            ['Música', 'Llega a la ciudad una banda de rock nacional con su gira aniversario', 'El recital será en el estadio cubierto y las entradas ya están a la venta.'],
            ['Teatro', 'Se estrena una obra escrita y protagonizada por actores locales', 'La puesta tendrá funciones todos los sábados de noviembre en la sala municipal.'],
        ],
        'Economía' => [
            ['Indicadores', 'La inflación de septiembre fue del 1,9% y acumula 23,6% en el año', 'Los rubros que más aumentaron fueron vivienda, servicios y transporte, según el informe oficial.'],
            ['Comercio', 'Las ventas minoristas crecieron por segundo mes consecutivo', 'La cámara de comercio local destacó el impacto de las promociones con tarjeta y las cuotas sin interés.'],
            ['Empleo', 'Una empresa local abrirá 50 nuevos puestos de trabajo', 'La firma ampliará su planta industrial y recibirá currículums hasta fin de mes.'],
            ['Servicios', 'Anunciaron un aumento en las tarifas de colectivos', 'El nuevo valor del boleto regirá desde el primer día del mes próximo.'],
        ],
        'Agro' => [
            ['Campo', 'La siembra de maíz avanza con buenas condiciones de humedad', 'Los productores de la zona ya implantaron cerca del 40% de la superficie prevista.'],
            ['Ganadería', 'El remate anual de hacienda superó las expectativas', 'Se comercializaron más de 3.000 cabezas con precios por encima del promedio regional.'],
        ],
        'Nacionales' => [
            ['País', 'El Gobierno nacional anunció cambios en el régimen de subsidios al gas', 'La medida alcanza a usuarios residenciales de varias provincias y comenzará a regir en enero.'],
            ['País', 'Médicos de la obra social nacional realizan un paro de 96 horas', 'Reclaman la actualización de honorarios y garantizan la atención de urgencias.'],
        ],
        'Necrológicas' => [
            ['Sepelios', 'Participaciones de sepelio del día', 'Listado de fallecimientos informados por las empresas de servicios fúnebres de la ciudad.'],
        ],
    ];

    /** Párrafos genéricos para armar el cuerpo de las notas. */
    private array $parrafos = [
        'Según informaron las autoridades, la iniciativa forma parte de un plan más amplio que se viene desarrollando desde principios de año y que busca dar respuesta a una demanda histórica de los vecinos.',
        'Durante la jornada, distintos referentes de la comunidad destacaron la importancia del trabajo conjunto entre el Estado, las instituciones intermedias y el sector privado para alcanzar estos resultados.',
        'Los interesados en obtener más información pueden acercarse a las oficinas correspondientes de lunes a viernes, en el horario de 8 a 14, o comunicarse a través de los canales oficiales.',
        'Fuentes consultadas por este medio señalaron que en las próximas semanas se darán a conocer nuevos detalles, y que el objetivo es que la propuesta llegue a la mayor cantidad de personas posible.',
        '"Es un paso muy importante para toda la ciudad y queremos que los vecinos se sientan parte", expresó uno de los responsables, quien además agradeció el acompañamiento recibido.',
        'El anuncio generó expectativa entre los distintos sectores involucrados, que valoraron la decisión aunque advirtieron que todavía quedan aspectos por resolver.',
        'Cabe recordar que en ediciones anteriores la convocatoria tuvo una respuesta muy positiva, lo que motivó a los organizadores a ampliar la propuesta para este año.',
        'Desde el área correspondiente indicaron que se realizará un seguimiento permanente y que se evaluarán los resultados para definir los próximos pasos.',
    ];

    private array $tagsEjemplo = [
        'Fiesta aniversario', 'Concejo Deliberante', 'Tránsito', 'Liga regional',
        'Cultura', 'Inflación', 'Obras públicas', 'Educación', 'Salud', 'Clima',
    ];

    public function run(): void
    {
        // Limpieza de notas anteriores (solo en datos de ejemplo)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('visitas_diarias')->truncate();
        DB::table('noticia_tag')->truncate();
        DB::table('noticias')->truncate();
        DB::table('tags')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $tags = collect($this->tagsEjemplo)->map(fn ($nombre) => Tag::obtenerOCrear($nombre));
        $autores = User::pluck('id');
        $categorias = Categoria::pluck('id', 'nombre');

        // Armamos la lista completa y la mezclamos para que las fechas queden intercaladas
        $notas = [];
        foreach ($this->titulos as $categoria => $items) {
            foreach ($items as $item) {
                $notas[] = [$categoria, ...$item];
            }
        }
        shuffle($notas);

        $fecha = now()->subMinutes(20);
        $foto = 10;

        foreach ($notas as $i => [$categoria, $volanta, $titulo, $bajada]) {
            $noticia = Noticia::create([
                'categoria_id' => $categorias[$categoria],
                'user_id'      => $autores->random(),
                'volanta'      => $volanta,
                'titulo'       => $titulo,
                'bajada'       => $bajada,
                'cuerpo'       => $this->armarCuerpo($bajada),
                'imagen'       => 'https://picsum.photos/seed/portal' . $foto++ . '/1200/675',
                'epigrafe'     => 'Imagen ilustrativa.',
                'estado'       => Noticia::ESTADO_PUBLICADA,
                'publicada_at' => $fecha->copy(),
                'destacada'    => $i < 3,           // las 3 más recientes quedan destacadas
            ]);

            $noticia->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
            $this->generarVisitas($noticia);

            // Cada nota es entre 40 minutos y 3 horas más vieja que la anterior
            $fecha->subMinutes(rand(40, 180));
        }

        // Una nota en borrador y una programada, para probar el panel
        Noticia::create([
            'categoria_id' => $categorias['Información General'],
            'user_id'      => $autores->first(),
            'volanta'      => 'Borrador',
            'titulo'       => 'Nota en borrador: no debe verse en el sitio',
            'bajada'       => 'Esta nota sirve para probar que los borradores no aparecen en la portada.',
            'cuerpo'       => '<p>Contenido de prueba.</p>',
            'estado'       => Noticia::ESTADO_BORRADOR,
        ]);

        Noticia::create([
            'categoria_id' => $categorias['Deportes'],
            'user_id'      => $autores->first(),
            'volanta'      => 'Programada',
            'titulo'       => 'Nota programada para mañana: todavía no debe verse',
            'bajada'       => 'Esta nota sirve para probar la publicación programada.',
            'cuerpo'       => '<p>Contenido de prueba.</p>',
            'imagen'       => 'https://picsum.photos/seed/portal99/1200/675',
            'estado'       => Noticia::ESTADO_PUBLICADA,
            'publicada_at' => now()->addDay()->setTime(8, 0),
        ]);
    }

    private function armarCuerpo(string $bajada): string
    {
        $parrafos = collect($this->parrafos)->shuffle()->take(rand(4, 6));

        $html = '<p><strong>' . e($bajada) . '</strong></p>';
        foreach ($parrafos as $j => $parrafo) {
            $html .= '<p>' . e($parrafo) . '</p>';
            if ($j === 1) {
                $html .= '<h2>Los detalles</h2>';
            }
        }

        return $html;
    }

    /** Visitas aleatorias por día desde la publicación hasta hoy. */
    private function generarVisitas(Noticia $noticia): void
    {
        $total = 0;
        $filas = [];
        $dia = $noticia->publicada_at->copy()->startOfDay();

        while ($dia->lte(today())) {
            $cantidad = rand(30, 900);
            $filas[] = ['noticia_id' => $noticia->id, 'fecha' => $dia->toDateString(), 'cantidad' => $cantidad];
            $total += $cantidad;
            $dia->addDay();
        }

        DB::table('visitas_diarias')->insert($filas);
        $noticia->forceFill(['visitas' => $total])->saveQuietly();
    }
}
