<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Etiquetas libres de las notas (ej: "Fiesta de Olavarría", "Elecciones 2027")
 * y su tabla pivote con noticias.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        Schema::create('noticia_tag', function (Blueprint $table) {
            $table->foreignId('noticia_id')->constrained('noticias')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['noticia_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticia_tag');
        Schema::dropIfExists('tags');
    }
};
