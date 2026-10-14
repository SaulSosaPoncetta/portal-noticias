<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Banners publicitarios.
 *
 * posicion:
 *  - header        : banner ancho debajo del menú
 *  - sidebar       : columna lateral
 *  - entre_bloques : entre las secciones de la portada
 *  - nota          : dentro de la página de la nota
 *  - footer        : arriba del pie de página
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);                       // anunciante / referencia interna
            $table->enum('posicion', ['header', 'sidebar', 'entre_bloques', 'nota', 'footer']);
            $table->string('imagen');                            // desktop
            $table->string('imagen_mobile')->nullable();         // opcional para celulares
            $table->string('url')->nullable();                   // link al hacer clic
            $table->date('desde')->nullable();
            $table->date('hasta')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->unsignedInteger('clics')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['posicion', 'activa', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicidades');
    }
};
