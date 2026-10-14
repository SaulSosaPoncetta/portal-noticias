<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Secciones del diario: Deportes, Política, Policiales, Espectáculos, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('slug', 100)->unique();
            $table->string('descripcion')->nullable();          // para SEO de la página de sección
            $table->string('color', 7)->default('#d62828');      // color de la volanta/etiqueta
            $table->unsignedSmallInteger('orden')->default(0);   // orden en menú y en portada
            $table->boolean('en_menu')->default(true);           // aparece en la barra de navegación
            $table->boolean('en_portada')->default(true);        // tiene bloque propio en la home
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['activa', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
