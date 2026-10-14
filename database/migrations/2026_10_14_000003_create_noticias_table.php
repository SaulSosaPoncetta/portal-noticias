<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notas del diario.
 *
 * - estado: borrador | publicada
 * - publicada_at: fecha/hora de publicación. Si es futura, la nota queda programada.
 * - destacada: candidata a nota principal de portada.
 * - orden_portada: posición manual en la grilla de portada (null = orden por fecha).
 * - visitas: contador total (las "más leídas" por período usan visitas_diarias).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('volanta', 80)->nullable();           // texto chico arriba del título
            $table->string('titulo', 200);
            $table->string('slug', 220)->unique();
            $table->text('bajada')->nullable();                  // copete / resumen
            $table->longText('cuerpo');

            $table->string('imagen')->nullable();                // ruta en storage/app/public
            $table->string('epigrafe')->nullable();              // pie de foto
            $table->string('video_url')->nullable();             // YouTube opcional

            $table->enum('estado', ['borrador', 'publicada'])->default('borrador');
            $table->dateTime('publicada_at')->nullable();
            $table->boolean('destacada')->default(false);
            $table->unsignedSmallInteger('orden_portada')->nullable();
            $table->unsignedInteger('visitas')->default(0);

            // SEO opcional (si queda vacío se usa título y bajada)
            $table->string('meta_titulo')->nullable();
            $table->string('meta_descripcion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'publicada_at']);
            $table->index(['categoria_id', 'estado', 'publicada_at']);
            $table->index(['destacada', 'publicada_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
